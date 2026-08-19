# BulkWhat — School WhatsApp Bulk Messaging System

## Table of Contents

1. [Product Overview](#1-product-overview)
2. [Key Features](#2-key-features)
3. [Technology Stack & System Architecture](#3-technology-stack--system-architecture)
4. [Database Schema & Model Relationships](#4-database-schema--model-relationships)
5. [WhatsApp API Integration](#5-whatsapp-api-integration)
6. [Personalization Engine & Merge Fields](#6-personalization-engine--merge-fields)
7. [Installation & Setup Guide](#7-installation--setup-guide)
8. [Configuration & Environment Variables](#8-configuration--environment-variables)
9. [User Workflow Guide](#9-user-workflow-guide)
10. [Security & Best Practices](#10-security--best-practices)

---

# 1. Product Overview

**BulkWhat** is a web-based bulk WhatsApp messaging system designed for schools, universities, and educational institutions.

The system allows administrators to upload student contact information from **Excel or CSV files**, organize students into categories, create reusable message templates, personalize messages using student information, and send bulk WhatsApp notifications through the **SMess WhatsApp API**.

BulkWhat is designed to provide a faster, more personalized alternative to traditional SMS communication.

### Primary Objectives

* Reduce reliance on traditional SMS communication.
* Enable schools to communicate with students through WhatsApp.
* Allow administrators to manage large student contact lists efficiently.
* Support personalized messages using student data.
* Provide reusable message templates.
* Organize students into categories and subcategories.
* Track campaign progress and message delivery status.
* Maintain detailed records of successful and failed messages.

---

# 2. Key Features

## 2.1 WhatsApp API Integration

BulkWhat integrates with the **SMess WhatsApp API** to dispatch WhatsApp messages.

### Features

* Bulk WhatsApp message delivery.
* Automatic Ghanaian phone number formatting.
* API authentication using an API key.
* Message delivery tracking.
* Error handling and logging.
* WhatsApp message ID tracking.

### Phone Number Formatting

The system automatically converts Ghanaian local numbers into international format.

Example:

`0241234567`

becomes:

`+233241234567`

The formatting process:

1. Removes spaces, brackets, hyphens, and other non-numeric characters.
2. Detects Ghanaian local numbers beginning with `0`.
3. Replaces the leading `0` with `+233`.
4. Ensures the final number is in international format.

---

# 2.2 Student Category Management

BulkWhat supports a hierarchical category system for organizing students.

### Example Category Structure

```text
Students
│
├── Undergraduate Students
│   ├── Regular Students
│   ├── Evening Students
│   └── Weekend Students
│
├── Postgraduate Students
│   ├── Masters Students
│   └── PhD Students
│
├── Freshers / New Students
│
└── Faculties & Schools
    ├── Faculty of Computing & Information Systems
    ├── Faculty of Engineering
    └── Business School
```

### Category Features

* Create parent and child categories.
* Upload student data directly into a category.
* Update existing category data.
* Assign students to multiple categories.
* Target an entire parent category.
* Automatically include students from child categories when targeting a parent category.

For example, targeting **Undergraduate Students** can automatically include:

* Regular Students
* Evening Students
* Weekend Students

---

# 2.3 Excel & CSV Import

Administrators can upload student contact information using Excel or CSV files.

Example columns:

| Name        | WhatsApp Number | Index Number | Programme            |
| ----------- | --------------- | ------------ | -------------------- |
| John Mensah | 0241234567      | 10234567     | BSc IT               |
| Ama Boateng | 0551234567      | 10234568     | BSc Computer Science |

During import, BulkWhat:

1. Reads the uploaded file.
2. Detects available columns.
3. Identifies the phone number column.
4. Validates student records.
5. Formats phone numbers.
6. Stores the original student data.
7. Assigns students to the selected category.
8. Records valid and invalid entries.
9. Displays an import summary.

---

# 2.4 Message Templates

Administrators can create reusable WhatsApp message templates.

Templates can be organized into categories such as:

* Application Status
* Examination & Results
* Course Registration
* Fees & Payments
* School Announcements
* General Notifications

### Example Template

```text
Dear [Student Name],

Your examination results for [Programme] are now available.

Please log into the student portal to view your results.

Thank you.
```

Administrators can:

* Create templates.
* Edit templates.
* Delete templates.
* Search templates.
* Categorize templates.
* Load templates directly into a campaign.
* Customize a template before sending.

---

# 2.5 Personalized Messages

BulkWhat supports dynamic merge fields that allow each student to receive a personalized message.

For example:

```text
Dear [Student Name],

Your results for [Programme] are now available.
```

For John:

```text
Dear John Mensah,

Your results for BSc Information Technology are now available.
```

The same campaign can therefore contain different personalized messages for every recipient.

---

# 2.6 Campaign Composer

The Campaign Composer allows administrators to prepare and send WhatsApp campaigns.

Administrators can:

* Enter a campaign name.
* Select a student category.
* Select a specific import.
* Write a custom message.
* Load a saved template.
* Edit the loaded template.
* Insert merge fields.
* Preview the message.
* Send a test message.
* Start the campaign.

---

# 2.7 Live WhatsApp Preview

BulkWhat provides a WhatsApp-style preview while composing a message.

The preview updates automatically as the administrator types.

Example:

**Message Composer**

```text
Dear [Student Name],

Your [Programme] results are now available.
```

**Preview**

```text
WhatsApp

Dear John Mensah,

Your BSc Information Technology results
are now available.
```

The preview uses sample student data to demonstrate how the final message will appear.

---

# 2.8 Campaign Tracking

Administrators can monitor campaign progress.

The system tracks:

* Total recipients.
* Messages sent.
* Failed messages.
* Pending messages.
* Campaign status.
* Campaign start time.
* Campaign completion time.
* WhatsApp message IDs.
* Error messages.

Campaign statuses include:

* `draft`
* `sending`
* `completed`
* `failed`

---

# 3. Technology Stack & System Architecture

| Component                | Technology                     |
| ------------------------ | ------------------------------ |
| Framework                | Laravel 13.x                   |
| Backend Language         | PHP 8.4                        |
| Authentication           | Laravel Breeze                 |
| Frontend                 | Blade                          |
| Styling                  | Tailwind CSS / Vanilla CSS     |
| JavaScript               | Alpine.js                      |
| Database                 | SQLite / MySQL                 |
| ORM                      | Laravel Eloquent               |
| Excel Processing         | Laravel Excel / PhpSpreadsheet |
| WhatsApp API             | SMess API                      |
| HTTP Client              | Laravel HTTP Client            |
| Queue System             | Laravel Queue                  |
| Batch Processing         | Laravel Bus Batching           |
| Dependency Manager       | Composer                       |
| Frontend Package Manager | NPM                            |

---

## 3.1 System Architecture

The system follows a Laravel-based MVC architecture.

```text
                    ┌─────────────────────┐
                    │      Admin User     │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │   Laravel Web App   │
                    │                     │
                    │ Controllers         │
                    │ Services            │
                    │ Models              │
                    │ Blade Views         │
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
              ▼                ▼                ▼
       ┌─────────────┐  ┌──────────────┐  ┌──────────────┐
       │   Database  │  │ Excel/CSV    │  │ Message      │
       │ SQLite/MySQL│  │ Import       │  │ Templates    │
       └─────────────┘  └──────────────┘  └──────────────┘
                               │
                               ▼
                     ┌──────────────────┐
                     │ Campaign Queue   │
                     │ & Batch Jobs     │
                     └────────┬─────────┘
                              │
                              ▼
                     ┌──────────────────┐
                     │  SMess WhatsApp  │
                     │       API        │
                     └────────┬─────────┘
                              │
                              ▼
                     ┌──────────────────┐
                     │ Student's       │
                     │ WhatsApp        │
                     └──────────────────┘
```

---

# 4. Database Schema & Model Relationships

The core database entities are:

* Users
* Student Categories
* Template Categories
* Templates
* Imports
* Recipients
* Campaigns
* Messages

### Relationship Overview

```text
Users
 │
 ├── Student Categories
 │       │
 │       └── Recipients
 │
 ├── Template Categories
 │       │
 │       └── Templates
 │
 ├── Imports
 │       │
 │       └── Recipients
 │
 └── Campaigns
         │
         └── Messages
```

---

## 4.1 `users`

Stores administrator accounts.

| Field        | Description                |
| ------------ | -------------------------- |
| `id`         | Primary key                |
| `name`       | Administrator name         |
| `email`      | Login email                |
| `password`   | Hashed password            |
| `timestamps` | Created/updated timestamps |

---

## 4.2 `student_categories`

Stores the hierarchical student categories.

| Field         | Description                |
| ------------- | -------------------------- |
| `id`          | Primary key                |
| `user_id`     | Owner/admin                |
| `parent_id`   | Parent category            |
| `name`        | Category name              |
| `slug`        | URL-friendly name          |
| `type`        | Category type              |
| `description` | Category description       |
| `timestamps`  | Created/updated timestamps |

The `parent_id` field allows categories to reference other categories within the same table.

---

## 4.3 `template_categories`

Stores template categories.

| Field         | Description                |
| ------------- | -------------------------- |
| `id`          | Primary key                |
| `user_id`     | Owner/admin                |
| `name`        | Category name              |
| `slug`        | URL-friendly name          |
| `description` | Category description       |
| `timestamps`  | Created/updated timestamps |

---

## 4.4 `templates`

Stores reusable message templates.

| Field                  | Description                |
| ---------------------- | -------------------------- |
| `id`                   | Primary key                |
| `user_id`              | Owner/admin                |
| `template_category_id` | Template category          |
| `title`                | Template title             |
| `body`                 | Message body               |
| `placeholders`         | JSON list of merge fields  |
| `timestamps`           | Created/updated timestamps |

---

## 4.5 `imports`

Stores uploaded Excel/CSV files.

| Field                 | Description                |
| --------------------- | -------------------------- |
| `id`                  | Primary key                |
| `user_id`             | Uploading administrator    |
| `student_category_id` | Target category            |
| `original_filename`   | Original uploaded filename |
| `stored_path`         | File storage path          |
| `columns`             | JSON list of columns       |
| `phone_column`        | Phone number column        |
| `total_records`       | Total imported rows        |
| `valid_records`       | Valid records              |
| `invalid_records`     | Invalid records            |
| `status`              | Import status              |
| `timestamps`          | Created/updated timestamps |

---

## 4.6 `recipients`

Stores individual student contact records.

| Field               | Description                         |
| ------------------- | ----------------------------------- |
| `id`                | Primary key                         |
| `import_id`         | Source import                       |
| `phone_number`      | Formatted phone number              |
| `data`              | JSON containing student information |
| `is_valid`          | Validation status                   |
| `validation_errors` | JSON validation errors              |
| `timestamps`        | Created/updated timestamps          |

---

## 4.7 `recipient_student_category`

Pivot table connecting recipients to categories.

| Field                 | Description |
| --------------------- | ----------- |
| `recipient_id`        | Recipient   |
| `student_category_id` | Category    |

Composite primary key:

```text
recipient_id + student_category_id
```

This allows one student to belong to multiple categories.

---

## 4.8 `campaigns`

Stores bulk messaging campaigns.

| Field                 | Description                |
| --------------------- | -------------------------- |
| `id`                  | Primary key                |
| `user_id`             | Campaign creator           |
| `import_id`           | Optional source import     |
| `student_category_id` | Optional target category   |
| `name`                | Campaign name              |
| `message_template`    | Message content            |
| `total_recipients`    | Number of recipients       |
| `sent_count`          | Successful messages        |
| `failed_count`        | Failed messages            |
| `status`              | Campaign status            |
| `started_at`          | Start time                 |
| `completed_at`        | Completion time            |
| `timestamps`          | Created/updated timestamps |

---

## 4.9 `messages`

Stores individual message delivery records.

| Field                  | Description                |
| ---------------------- | -------------------------- |
| `id`                   | Primary key                |
| `campaign_id`          | Campaign                   |
| `recipient_id`         | Recipient                  |
| `phone_number`         | Destination number         |
| `personalized_message` | Final personalized message |
| `status`               | Message status             |
| `whatsapp_message_id`  | API message ID             |
| `error_message`        | Failure reason             |
| `sent_at`              | Sending timestamp          |
| `timestamps`           | Created/updated timestamps |

Message statuses:

* `pending`
* `sent`
* `failed`

---

# 5. WhatsApp API Integration

BulkWhat communicates with the SMess WhatsApp API through a dedicated Laravel service:

```text
App\Services\SMessService
```

The service is responsible for:

* Formatting phone numbers.
* Sending API requests.
* Authenticating requests.
* Processing API responses.
* Returning success/failure information.
* Recording API message IDs.
* Handling errors.

---

## 5.1 API Endpoint

```text
POST https://smess.io/api/send
```

### Authentication

The API key is stored securely in the application's `.env` file.

```text
X-API-Key: {SMESS_API_KEY}
```

### Request

```json
{
    "recipient": "+233241234567",
    "text": "Dear John, your results are now available."
}
```

---

## 5.2 SMess Service

The application should keep API communication inside `SMessService` instead of calling the API directly from controllers.

Conceptually:

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

    if ($response->successful() && ($data['success'] ?? false)) {
        return [
            'success' => true,
            'message_id' => $data['data']['queue_id']
                ?? $data['queue_id']
                ?? null,
            'error' => null,
        ];
    }

    return [
        'success' => false,
        'message_id' => null,
        'error' => $data['message']
            ?? 'Failed to send WhatsApp message.',
    ];
}
```

---

# 6. Personalization Engine & Merge Fields

BulkWhat includes a personalization engine that dynamically replaces placeholders with student information.

The personalization logic is handled by:

```text
App\Services\MessageTemplateService
```

---

## 6.1 Supported Placeholder Formats

BulkWhat supports multiple placeholder formats, including:

```text
{{Name}}

[Name]

[Student Name]

{Name}

{Programme}

+Name+

+ Name +

%Name%
```

Angle-bracket placeholders may also be supported if enabled by the implementation.

---

## 6.2 Example

Suppose the imported student data contains:

```text
Name = John Mensah
Programme = BSc Information Technology
Index Number = 10234567
```

The administrator writes:

```text
Dear [Student Name],

Your results for [Programme] are now available.

Your index number is [Index Number].
```

BulkWhat generates:

```text
Dear John Mensah,

Your results for BSc Information Technology are now available.

Your index number is 10234567.
```

---

## 6.3 Placeholder Matching

The personalization engine uses several matching strategies.

### 1. Direct Match

The placeholder is matched directly against the Excel column.

Example:

```text
[Name]
```

matches:

```text
Name
```

### 2. Case-Insensitive Match

These are treated as equivalent:

```text
Name
name
NAME
```

### 3. Normalized Match

The system can remove spaces, underscores, and dashes when comparing field names.

For example:

```text
Student Name
student_name
student-name
```

can be treated as equivalent.

### 4. Synonym Matching

The system can map common placeholder names to likely Excel columns.

For example:

```text
Student Name
Name
Full Name
First Name
Student
```

can be used as possible matches for a student-name placeholder.

Similarly:

```text
Index Number
Index_Number
IndexNo
ID
```

can be considered when resolving an index-number placeholder.

For programme information:

```text
Programme
Program
Course
Department
```

can be considered equivalent candidates.

---

# 7. Installation & Setup Guide

## 7.1 Prerequisites

Before installing BulkWhat, ensure the following are available:

* PHP 8.4 or later
* Composer 2.x or later
* Node.js 18.x or later
* NPM
* SQLite or MySQL
* Git
* A valid SMess API account and API key

---

## 7.2 Clone the Project

```bash
git clone https://github.com/your-repository/bulkwhat.git

cd bulkwhat
```

Replace the repository URL with the actual BulkWhat repository.

---

## 7.3 Install PHP Dependencies

```bash
composer install
```

---

## 7.4 Install Frontend Dependencies

```bash
npm install
```

Build the frontend assets:

```bash
npm run build
```

During development, you can use:

```bash
npm run dev
```

---

## 7.5 Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

On Windows, if `cp` is unavailable, you can manually copy:

```text
.env.example
```

and rename the copy to:

```text
.env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

---

## 7.6 Configure Database

For SQLite, create:

```text
database/database.sqlite
```

Then configure:

```env
DB_CONNECTION=sqlite
```

For MySQL, configure the appropriate database credentials in `.env`.

---

## 7.7 Run Migrations

```bash
php artisan migrate
```

If seeders are configured:

```bash
php artisan db:seed
```

Or run the specific seeder:

```bash
php artisan db:seed --class=CategoryAndTemplateSeeder
```

---

## 7.8 Configure PHP Upload Limits

For large Excel/CSV files, update `php.ini`:

```ini
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 512M
```

Restart your PHP server after changing these values.

---

## 7.9 Start Laravel

Run:

```bash
php artisan serve
```

The application should be available at:

```text
http://localhost:8000
```

---

# 8. Configuration & Environment Variables

The `.env` file should contain configuration values such as:

```env
APP_NAME=BulkWhat
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

SMESS_API_KEY=your_smess_api_key
SMESS_BASE_URL=https://smess.io/api

DEFAULT_COUNTRY_CODE=233

QUEUE_CONNECTION=sync
```

### Important

**Never put a real API key directly inside source code or documentation that will be uploaded to GitHub.**

Do not commit:

```text
.env
```

to your repository.

Instead, commit:

```text
.env.example
```

with placeholder values:

```env
SMESS_API_KEY=
SMESS_BASE_URL=https://smess.io/api
DEFAULT_COUNTRY_CODE=233
QUEUE_CONNECTION=sync
```

If an API key has already been exposed publicly, it should be revoked or regenerated.

---

# 9. User Workflow Guide

## 9.1 Administrator Login

The administrator logs into BulkWhat using their registered credentials.

For development/testing, a seeded administrator account may be created.

**Do not use a default password in production.**

---

## 9.2 Upload Student Contacts

Navigate to:

```text
Student Categories
```

Select a category such as:

```text
Weekend Students
```

Click:

```text
Upload / Update Excel Data
```

Select the Excel or CSV file.

Example:

```text
Name
WhatsApp Number
Index Number
Programme
```

Click:

```text
Process & Save
```

BulkWhat then:

1. Reads the file.
2. Detects columns.
3. Identifies the phone column.
4. Validates records.
5. Formats phone numbers.
6. Saves recipients.
7. Assigns recipients to the selected category.
8. Displays the import summary.

---

# 9.3 Create a Message Template

Navigate to:

```text
Message Templates
```

Click:

```text
Create Template
```

Enter:

* Template title
* Template category
* Message body

Example:

```text
Dear [Student Name],

Your results for [Programme] are now available.

Please log into the student portal to view your results.

Thank you.
```

Click:

```text
Save Template
```

---

# 9.4 Create a Campaign

Navigate to:

```text
Campaigns
```

Click:

```text
New Campaign
```

Enter the campaign name.

Example:

```text
2026 Examination Results Notification
```

Select a target.

### Option A — Student Category

Example:

```text
Undergraduate Students
```

### Option B — Specific Import

Example:

```text
August 2026 Student Upload.xlsx
```

---

# 9.5 Compose the Message

The administrator can either:

### Option A — Write a New Message

Type the message directly into the composer.

### Option B — Load a Saved Template

Select an existing template.

The template can then be modified before sending.

---

# 9.6 Preview the Message

BulkWhat displays a WhatsApp-style preview.

The administrator can verify:

* Student name.
* Programme.
* Index number.
* Message formatting.
* Overall appearance.

---

# 9.7 Send a Test Message

Before launching the campaign, the administrator should send a test message to an authorized test number.

The administrator verifies that:

* The message was delivered.
* The phone number is correctly formatted.
* Merge fields are working correctly.
* The message looks correct on WhatsApp.

---

# 9.8 Send the Campaign

Once the test message has been verified:

```text
Send Campaign Now
```

BulkWhat creates the required message jobs and processes the campaign.

Each message is individually personalized and logged.

---

# 9.9 Monitor Campaign Progress

The administrator can monitor:

```text
Total Recipients
Sent
Failed
Pending
Campaign Status
```

Example:

```text
Campaign: Examination Results

Total Recipients: 2,500
Sent: 2,350
Failed: 100
Pending: 50

Status: Sending
```

When processing is complete:

```text
Status: Completed
```

---

# 10. Security & Best Practices

Because BulkWhat handles student contact information, security should be treated as a core requirement.

## 10.1 API Key Security

* Store API keys in `.env`.
* Never hard-code API keys.
* Never commit `.env` to GitHub.
* Regenerate exposed API keys immediately.
* Use different API keys for development and production when possible.

---

## 10.2 Authentication

Administrators should authenticate before accessing:

* Student records.
* Categories.
* Imports.
* Templates.
* Campaigns.
* Message logs.

Use Laravel's authentication and authorization mechanisms to protect these resources.

---

## 10.3 File Upload Security

Uploaded files should be validated.

Recommended restrictions:

```text
Allowed extensions:
.xlsx
.xls
.csv
```

Also validate:

* Maximum file size.
* File MIME type.
* File structure.
* Required columns.
* Number of records.

Uploaded files should never be trusted simply because they have an Excel extension.

---

## 10.4 Student Data Protection

Student information should be treated as confidential.

Recommended practices:

* Restrict access to authorized administrators.
* Validate all user input.
* Avoid exposing student information in URLs.
* Use authorization policies.
* Protect database backups.
* Avoid logging unnecessary personal information.
* Use HTTPS in production.

---

## 10.5 Message Safety

Before sending a large campaign:

1. Preview the message.
2. Verify merge fields.
3. Send a test message.
4. Confirm the target category.
5. Confirm the recipient count.
6. Confirm the message content.
7. Start the campaign.

This reduces the risk of accidentally sending incorrect information to thousands of students.

---

# Future Improvements

Potential future versions of BulkWhat could include:

* Scheduled campaigns.
* Campaign pause/resume functionality.
* Delivery reports.
* Advanced analytics dashboard.
* Failed-message retry system.
* Multiple administrator roles.
* Role-based permissions.
* Contact deduplication.
* Contact search and filtering.
* Message scheduling.
* Campaign cancellation.
* WhatsApp media messages.
* Document and image attachments.
* Exportable campaign reports.
* Student opt-out management.
* Audit logs.
* Multi-school support.
* API/webhook support.
* Real-time campaign progress using Laravel broadcasting.

---

# Conclusion

**BulkWhat** provides a centralized platform for educational institutions to manage student contacts and communicate with students through personalized WhatsApp messages.

By combining Excel/CSV imports, hierarchical student categories, reusable templates, dynamic personalization, campaign management, queue-based processing, and WhatsApp API integration, BulkWhat provides a scalable foundation for modern school communication.

The system can begin as a local Laravel application for development and testing and later be deployed to a production server for use by schools and educational institutions.
