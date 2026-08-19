<?php

namespace App\Services;

class MessageTemplateService
{
    /**
     * Master regex matching all placeholder syntaxes:
     * {{Field}}, [Field], {Field}, <Field>, %Field%, +Field+, + fieldName +
     */
    private const PLACEHOLDER_REGEX = '/(\{\{\s*([^}]+?)\s*\}\}|\[\s*([^\]]+?)\s*\]|\{\s*([^}]+?)\s*\}|<\s*([^>]+?)\s*>|%\s*([^%]+?)\s*%|\+\s*([^+]+?)\s*\+)/i';

    /**
     * Extract all placeholder names from a template.
     */
    public function extractPlaceholders(string $template): array
    {
        preg_match_all(self::PLACEHOLDER_REGEX, $template, $matches, PREG_SET_ORDER);
        $placeholders = [];

        foreach ($matches as $match) {
            for ($i = 2; $i < count($match); $i++) {
                if (!empty($match[$i])) {
                    $placeholders[] = trim($match[$i]);
                    break;
                }
            }
        }

        return array_values(array_unique($placeholders));
    }

    /**
     * Validate that placeholders in template can be matched against available columns.
     */
    public function validateTemplate(string $template, array $availableColumns): array
    {
        $placeholders = $this->extractPlaceholders($template);
        $missing = [];

        foreach ($placeholders as $placeholder) {
            $val = $this->findValueForPlaceholder($placeholder, array_fill_keys($availableColumns, 'sample'));
            if ($val === null) {
                $missing[] = $placeholder;
            }
        }

        return [
            'valid' => empty($missing),
            'missing' => $missing,
        ];
    }

    /**
     * Replace placeholders in a template with actual recipient data.
     */
    public function personalizeMessage(string $template, array $recipientData): string
    {
        return preg_replace_callback(self::PLACEHOLDER_REGEX, function ($match) use ($recipientData) {
            $placeholderName = '';
            for ($i = 2; $i < count($match); $i++) {
                if (!empty($match[$i])) {
                    $placeholderName = trim($match[$i]);
                    break;
                }
            }

            if (empty($placeholderName)) {
                return '';
            }

            $value = $this->findValueForPlaceholder($placeholderName, $recipientData);

            return $value !== null ? (string) $value : '';
        }, $template);
    }

    /**
     * Smart value resolution for a placeholder against a recipient data row.
     */
    public function findValueForPlaceholder(string $placeholderName, array $recipientData): ?string
    {
        // 1. Direct exact key match
        if (array_key_exists($placeholderName, $recipientData)) {
            return (string) $recipientData[$placeholderName];
        }

        // 2. Case-insensitive key match
        $lowerPlaceholder = strtolower($placeholderName);
        foreach ($recipientData as $key => $value) {
            if (strtolower($key) === $lowerPlaceholder) {
                return (string) $value;
            }
        }

        // 3. Clean alphanumeric match (stripping spaces, underscores, dashes)
        $cleanPlaceholder = preg_replace('/[^a-z0-9]/', '', $lowerPlaceholder);
        foreach ($recipientData as $key => $value) {
            $cleanKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));
            if ($cleanKey === $cleanPlaceholder) {
                return (string) $value;
            }
        }

        // 4. Synonym alias groups
        $nameAliases = ['name', 'studentname', 'fullname', 'firstname', 'lastname', 'fieldname', 'student', 'recipientname'];
        if (in_array($cleanPlaceholder, $nameAliases) || str_contains($lowerPlaceholder, 'name') || str_contains($lowerPlaceholder, 'student')) {
            foreach ($recipientData as $key => $value) {
                $cleanKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));
                if (in_array($cleanKey, ['name', 'studentname', 'fullname', 'student', 'firstname', 'nameofstudent', 'recipient'])) {
                    return (string) $value;
                }
            }
            // Fallback to first non-phone, non-id key if placeholder is "name" / "fieldname"
            foreach ($recipientData as $key => $value) {
                $cleanKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));
                if (!in_array($cleanKey, ['whatsappnumber', 'phone', 'mobile', 'email', 'indexnumber', 'id'])) {
                    return (string) $value;
                }
            }
        }

        $phoneAliases = ['phone', 'phonenumber', 'whatsapp', 'whatsappnumber', 'mobile', 'contact'];
        if (in_array($cleanPlaceholder, $phoneAliases) || str_contains($lowerPlaceholder, 'phone') || str_contains($lowerPlaceholder, 'whatsapp')) {
            foreach ($recipientData as $key => $value) {
                $cleanKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));
                if (in_array($cleanKey, ['whatsappnumber', 'phone', 'phonenumber', 'mobile', 'contact', 'whatsapp'])) {
                    return (string) $value;
                }
            }
        }

        $idAliases = ['index', 'indexnumber', 'indexno', 'studentid', 'id', 'regno', 'registrationnumber'];
        if (in_array($cleanPlaceholder, $idAliases) || str_contains($lowerPlaceholder, 'index') || str_contains($lowerPlaceholder, 'id')) {
            foreach ($recipientData as $key => $value) {
                $cleanKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));
                if (in_array($cleanKey, ['indexnumber', 'indexno', 'index', 'studentid', 'id'])) {
                    return (string) $value;
                }
            }
        }

        return null;
    }

    /**
     * Generate preview messages for a list of recipients.
     */
    public function generatePreviews(string $template, array $recipientsData, int $limit = 5): array
    {
        $previews = [];
        $count = 0;

        foreach ($recipientsData as $recipientData) {
            if ($count >= $limit) {
                break;
            }

            $previews[] = [
                'recipient' => $recipientData,
                'message' => $this->personalizeMessage($template, $recipientData),
            ];

            $count++;
        }

        return $previews;
    }
}
