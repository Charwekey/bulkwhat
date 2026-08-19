# School WhatsApp Bulk Messaging System (BulkWhat)

**BulkWhat** is a web-based bulk WhatsApp messaging application built for school administrators to upload student contact information from Excel/CSV files, organize students into categories, compose personalized messages, and send bulk notifications through the **SMess WhatsApp API**.

---

## Table of Contents

1. [Product Overview](#1-product-overview)
2. [Key Features](#2-key-features)
3. [Technology Stack & System Architecture](#3-technology-stack--system-architecture)
4. [Database Schema & Model Relationships](#4-database-schema--model-relationships)
5. [WhatsApp API Integration (SMess API)](#5-whatsapp-api-integration-smess-api)
6. [Personalization Engine & Merge Fields](#6-personalization-engine--merge-fields)
7. [Installation & Setup Guide](#7-installation--setup-guide)
8. [Configuration & Environment Variables](#8-configuration--environment-variables)
9. [User Workflow Guide](#9-user-workflow-guide)

---

## 1. Product Overview

The primary goal of **BulkWhat** is to replace slow and costly SMS channels with direct, personalized WhatsApp communications. Administrators can upload student contact lists from Excel/CSV files, organize students into hierarchical categories (such as *Undergraduate -> Weekend Students* or *Faculty of Engineering*), use reusable message templates, and monitor campaign progress in real-time.

---

## 2. Key Features

### 🟢 SMess WhatsApp API Integration
- Direct message dispatching via the **SMess WhatsApp API** (`https://smess.io/api/send`).
- Automatic phone number formatting for local Ghanaian numbers (`024XXXXXXX` -> `+23324XXXXXXX`).
- Detailed delivery logging and error tracking (e.g. invalid numbers, API key failures).

### 📂 Dynamic Student Contact Categories
- **Hierarchical Category Tree**:
  - **Level of Study**: Undergraduate Students (Parent) -> Regular, Evening, Weekend Students.
  - **Postgraduate Students**: Masters, PhD.
  - **Freshers / New Students**.
  - **Faculties & Schools**: Faculty of Computing & Information Systems, Faculty of Engineering, Business School.
- **Category-Specific Uploads & Updates**: Upload an Excel/CSV file directly into a category (e.g. *Weekend Students*). Re-uploading updates the category's contact database without affecting other categories.
- **Subcategory Inheritance**: Targeting a parent category like *Undergraduate Students* in a campaign automatically includes all recipients from *Regular*, *Evening*, and *Weekend* subcategories.

### 📝 Message Templates & Categories
- Reusable message templates organized into categories (*Application Status*, *Examination & Results*, *Course Registration*, *General Announcements*).
- Full Template Management (Create, Edit, Search, Delete).
- **One-Click Loading**: Insert saved templates into the campaign composer with a single click or dropdown selection, with full freedom to customize the message body before sending.

### 🔀 Multi-Format Merge Field Personalization
- Automatically replaces dynamic fields with student data from uploaded Excel columns.
- Supports **all common placeholder syntaxes**:
  - `[Student Name]` or `[Name]`
  - `{{Name}}` or `{{Student Name}}`
  - `{Student Name}` or `{Name}`
  - `+ fieldName +` or `+fieldName+`
  - `%Name%` or `<Name>`
- **Smart Synonym Matching**: Maps placeholders like `[Student Name]` or `+ fieldName +` to Excel columns named `Name`, `Student Name`, `Full Name`, `First Name`, or `Student`.

### 📱 Campaign Composer & Live WhatsApp Preview
- **Two-Way Drafting Modes**: Switch between typing a custom message from scratch or picking from saved templates.
- **Live WhatsApp Chat Preview**: Interactive chat bubble preview that updates live as you type, rendering sample student data in bold.
- **Targeted Campaign Creation**: Target a campaign by **Student Category** or a specific **Excel Upload**.
- **Asynchronous Batch Execution**: Background job processing via Laravel Queue and Bus Batches (`ProcessBulkCampaignJob` and `SendWhatsAppMessageJob`).

---

## 3. Technology Stack & System Architecture

| Component | Technology |
| :--- | :--- |
| **Framework** | Laravel 13.x (PHP 8.4) |
| **Authentication** | Laravel Breeze (Blade Stack) |
| **Database** | SQLite / MySQL (Eloquent ORM) |
| **Styling & UI** | Vanilla CSS, Tailwind CSS, Alpine.js |
| **Excel Parser** | PhpSpreadsheet & `maatwebsite/excel` (v4.0) |
| **WhatsApp Service** | `SMessService` (HTTP client wrapper for SMess API) |
| **Queue & Batching** | Laravel Bus Batching & Queue Jobs |

---

## 4. Database Schema & Model Relationships

```
                     ┌──────────────────┐
                     │      Users       │
                     └────────┬─────────┘
                              │
          ┌───────────────────┼───────────────────┐
          │ 1                 │ 1                 │ 1
          ▼                   ▼                   ▼
┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐
│StudentCategory   │ │TemplateCategory  │ │     Import       │
└────────┬─────────┘ └────────┬─────────┘ └────────┬─────────┘
         │ 1                  │ 1                  │ 1
         ▼ *                  ▼ *                  ▼ *
┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐
│   Campaign       │ │     Template     │ │    Recipient     │
└────────┬─────────┘ └──────────────────┘ └────────┬─────────┘
         │ 1                                       │ 1
         ▼ *                                       ▼ *
┌────────────────────────────────────────────────────────────┐
│                          Message                           │
└────────────────────────────────────────────────────────────┘
```

### Table Definitions

1. **`student_categories`**:
   - `id`, `user_id`, `parent_id` (foreignId to self for hierarchy), `name`, `slug`, `type` (`level`, `study_mode`, `faculty`, `custom`), `description`, `timestamps`.
2. **`template_categories`**:
   - `id`, `user_id`, `name`, `slug`, `description`, `timestamps`.
3. **`templates`**:
   - `id`, `user_id`, `template_category_id`, `title`, `body`, `placeholders` (json), `timestamps`.
4. **`imports`**:
   - `id`, `user_id`, `student_category_id`, `original_filename`, `stored_path`, `columns` (json), `phone_column`, `total_records`, `valid_records`, `invalid_records`, `status`, `timestamps`.
5. **`recipients`**:
   - `id`, `import_id`, `phone_number`, `data` (json containing all raw Excel fields), `is_valid`, `validation_errors` (json), `timestamps`.
6. **`recipient_student_category`** (Pivot Table):
   - `recipient_id`, `student_category_id` (Primary Key: `[recipient_id, student_category_id]`).
7. **`campaigns`**:
   - `id`, `user_id`, `import_id` (nullable), `student_category_id` (nullable), `name`, `message_template`, `total_recipients`, `sent_count`, `failed_count`, `status` (`draft`, `sending`, `completed`, `failed`), `started_at`, `completed_at`, `timestamps`.
8. **`messages`**:
   - `id`, `campaign_id`, `recipient_id`, `phone_number`, `personalized_message`, `status` (`pending`, `sent`, `failed`), `whatsapp_message_id`, `error_message`, `sent_at`, `timestamps`.

---

## 5. WhatsApp API Integration (SMess API)

The system integrates with **SMess WhatsApp API** via `App\Services\SMessService`.

### API Protocol Specification
- **Base Endpoint**: `POST https://smess.io/api/send`
- **Authentication**: Header `X-API-Key: {SMESS_API_KEY}` (and fallback `apikey` form payload).
- **Request Parameters**:
  - `recipient`: Phone number in E.164 format (e.g. `+233241234567`).
  - `text`: Personalized message body.

### Service Implementation (`App\Services\SMessService`)
```php
public function sendMessage(string $phoneNumber, string $message): array
{
    $formattedPhone = $this->formatPhoneNumber($phoneNumber);

    $response = Http::withHeaders([
        'X-API-Key' => $this->apiKey,
        'Accept' => 'application/json',
    ])->post($this->baseUrl . '/send', [
        'recipient' => $formattedPhone,
        'text' => $message,
    ]);

    $data = $response->json();

    if ($response->successful() && isset($data['success']) && $data['success']) {
        return [
            'success' => true,
            'message_id' => $data['data']['queue_id'] ?? $data['queue_id'] ?? null,
            'error' => null,
        ];
    }

    return [
        'success' => false,
        'message_id' => null,
        'error' => $data['message'] ?? 'Failed to send WhatsApp message via SMess API.',
    ];
}
```

### Phone Number Auto-Formatting Logic
- Strips non-numeric characters.
- Converts local Ghanaian numbers starting with `0` (e.g. `0241234567`) to international format with country code `233` (`+233241234567`).
- Ensures leading `+` prefix for SMess API compatibility.

---

## 6. Personalization Engine & Merge Fields

The personalization logic in `App\Services\MessageTemplateService` uses regex matching and smart synonym lookups to merge student data dynamically into templates.

### Supported Syntaxes
```
{{Name}}        | Double curly braces
[Student Name]  | Square brackets
{Programme}     | Single curly braces
+ fieldName +   | Plus-delimited field names
%Index Number%  | Percent signs
<Student Name>  | Angle brackets
```

### Matching Algorithm (`findValueForPlaceholder`)
1. **Direct Match**: Exact key match in Excel row data (e.g. `$recipientData['Name']`).
2. **Case-Insensitive Match**: `strtolower($key) === strtolower($placeholder)`.
3. **Alphanumeric Clean Match**: Strips spaces, dashes, and underscores (e.g. `student_name` matches `Student Name`).
4. **Synonym Group Mapping**:
   - `[Student Name]`, `[Name]`, `+ fieldName +` -> maps to Excel column `Name`, `Student Name`, `Full Name`, `First Name`, or `Student`.
   - `[Index Number]`, `[Index]` -> maps to `Index Number`, `Index_Number`, `IndexNo`, `ID`.
   - `[Programme]`, `[Course]` -> maps to `Programme`, `Program`, `Course`, `Department`.

---

## 7. Installation & Setup Guide

### Prerequisites
- PHP >= 8.4
- Composer >= 2.x
- Node.js >= 18.x & NPM
- SQLite or MySQL Database

### Step-by-Step Installation

1. **Clone Repository**:
   ```bash
   git clone https://github.com/your-repo/bulkwhat.git
   cd bulkwhat
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install & Build Frontend Assets**:
   ```bash
   npm install
   npm run build
   ```

4. **Environment Setup**:
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure PHP Upload Limits**:
   In your `php.ini` configuration (e.g., Herd/XAMPP):
   ```ini
   upload_max_filesize = 64M
   post_max_size = 64M
   memory_limit = 512M
   ```

6. **Run Migrations & Seed Default Data**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=CategoryAndTemplateSeeder
   ```

7. **Start Development Server**:
   ```bash
   php artisan serve
   ```
   Access the app at **`http://localhost:8000`** (or `http://bulkwhat.test`).

---

## 8. Configuration & Environment Variables

Add your SMess credentials to `.env`:

```env
# SMess WhatsApp API Configuration
SMESS_API_KEY=SM-8E1CBDF6E346606F39C0D0A7645E17DE
SMESS_API_KEYS=SM-8E1CBDF6E346606F39C0D0A7645E17DE
SMESS_BASE_URL=https://smess.io/api

# Default country code for phone formatting (without +)
DEFAULT_COUNTRY_CODE=233

# Queue Connection (Use sync for instant local sending)
QUEUE_CONNECTION=sync
```

---

## 9. User Workflow Guide

### Default Admin Credentials
- **Email**: `admin@bulkwhat.com`
- **Password**: `password123`

### 1. Uploading Contacts by Category
1. Navigate to **Student Categories** (`/categories`).
2. Select a category (e.g. *Weekend Students* or *Faculty of Engineering*).
3. Click **Upload / Update Excel Data**.
4. Select your Excel/CSV file containing student columns (e.g., `Name`, `WhatsApp Number`, `Index Number`, `Programme`).
5. Click **Process & Save**. All student records will be parsed, validated, and assigned to that category.

### 2. Creating Message Templates
1. Navigate to **Message Templates** (`/templates`).
2. Click **Create Template**.
3. Fill in the title, category, and message body using placeholders (e.g. `Dear [Student Name], your results for [Programme] are out.`).
4. Click **Save Template**. Templates can be edited or deleted anytime directly from the template card.

### 3. Sending a Bulk WhatsApp Campaign
1. Navigate to **Campaigns** (`/campaigns`).
2. Click **New Campaign**.
3. Enter a campaign name and select your target:
   - **Option A**: Target a **Student Category** (e.g. *Weekend Students* or *Undergraduate Students*).
   - **Option B**: Target a specific **Excel Upload File**.
4. In the Campaign Composer:
   - Type a custom message OR select a saved template using **Quick Load Template** or **Select From Saved Templates**.
   - Customize the message text as needed.
   - Observe the live WhatsApp chat preview updating in real-time.
5. Click **Save Template & Preview Message**.
6. Send a **Test Message** to verify delivery on your own phone.
7. Click **Send Campaign Now** to dispatch bulk messages to all students in the category.
#   b u l k w h a t  
 