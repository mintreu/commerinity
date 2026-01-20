<?php

declare(strict_types=1);

namespace App\Filament\Resources\JobApplications\Schemas;

/**
 * Schema/Configuration for Job Application Excel Import.
 * This file contains metadata about the import structure.
 */
final class ImportSchema
{
    /**
     * User-friendly column headers for Excel template
     */
    public const HEADERS = [
        // Required columns
        'full_name',
        'email_address',
        'mobile_number',
        'user_type',
        'job_posting_title',
        'street_address',
        'city',
        'pin_code',
        'state_name',
        'country_name',
        'address_category',

        // Optional columns
        'gender',
        'date_of_birth',
        'brief_bio',
        'pan_number',
        'aadhaar_number',
        'guardian_name',
        'education_qualification',
        'skills',
        'work_experience',
        'referee_name',
        'referee_mobile',
        'payment_status',
        'payment_amount',
        'transaction_id',
        'verify_email',
        'verify_mobile',
        'activate_account',
    ];

    /**
     * Mapping from user-friendly name to internal database column name
     */
    public const HEADER_MAP = [
        'full_name' => 'name',
        'email_address' => 'email',
        'mobile_number' => 'mobile',
        'user_type' => 'type',
        'job_posting_title' => 'recruitment',
        'street_address' => 'addr_line1',
        'city' => 'city',
        'pin_code' => 'postal_code',
        'state_name' => 'state',
        'country_name' => 'country',
        'address_category' => 'address_type',
        'gender' => 'gender',
        'date_of_birth' => 'dob',
        'brief_bio' => 'bio',
        'pan_number' => 'pan_number',
        'aadhaar_number' => 'aadhaar_number',
        'guardian_name' => 'guardian_name',
        'education_qualification' => 'education',
        'skills' => 'skills',
        'work_experience' => 'experience',
        'referee_name' => 'reference_name',
        'referee_mobile' => 'reference_mobile',
        'payment_status' => 'is_paid',
        'payment_amount' => 'amount',
        'transaction_id' => 'transaction_id',
        'verify_email' => 'verify_email',
        'verify_mobile' => 'verify_mobile',
        'activate_account' => 'onboard_user',
    ];

    /**
     * Default values for optional fields
     */
    public const OPTIONAL_DEFAULTS = [
        'payment_status' => 'no',
        'verify_email' => 'yes',
        'verify_mobile' => 'yes',
        'activate_account' => 'yes',
    ];

    /**
     * Validation rules for each column
     */
    public const VALIDATION_RULES = [
        'full_name' => 'required|string|max:255',
        'email_address' => 'required|email|unique:users,email',
        'mobile_number' => 'required|digits:10|unique:users,mobile',
        'user_type' => 'required|in:regular,member,promoter,advisor,mentor,applicant',
        'job_posting_title' => 'required|string|exists:recruitments,title',
        'street_address' => 'required|string',
        'city' => 'required|string',
        'pin_code' => 'required|string',
        'state_name' => 'required|string',
        'country_name' => 'required|string',
        'address_category' => 'required|in:present,permanent,business',
        'pan_number' => 'nullable|string|size:10',
        'aadhaar_number' => 'nullable|string|size:12',
        'payment_status' => 'nullable|in:yes,no',
        'payment_amount' => 'nullable|numeric|min:0',
    ];

    /**
     * Get documentation for the import
     */
    public static function getDocumentation(): array
    {
        // Inline documentation - no external service needed
        return [
            'full_name' => [
                'description' => 'Full name of the applicant',
                'required' => true,
                'example' => 'Rahul Sharma',
            ],
            'email_address' => [
                'description' => 'Valid email address (must be unique)',
                'required' => true,
                'example' => 'rahul.sharma@example.com',
            ],
            'mobile_number' => [
                'description' => '10-digit mobile number (must be unique)',
                'required' => true,
                'example' => '9876543210',
            ],
        ];
    }

    /**
     * Get sample data row
     */
    public static function getSampleData(): array
    {
        return [
            [
                // **REQUIRED FIELDS - DO NOT DELETE THESE COLUMNS**
                'full_name' => 'Rahul Sharma',
                'email_address' => 'rahul.sharma@example.com',
                'mobile_number' => '9876543210',
                'user_type' => 'applicant',
                'job_posting_title' => 'Software Developer - Laravel', // Use job title or slug
                'street_address' => 'Street 12, ABC Nagar, Near City Mall',
                'city' => 'Delhi',
                'pin_code' => '110001',
                'state_name' => 'Delhi', // Full state name
                'country_name' => 'India', // Full country name
                'address_category' => 'present', // Options: present, permanent, business

                // **OPTIONAL FIELDS - You can leave these blank if not needed**
                'gender' => 'male', // male, female, other
                'date_of_birth' => '1998-05-21',
                'brief_bio' => '3 years experience in web development',

                // **KYC DETAILS (Optional but Recommended)**
                'pan_number' => 'ABCDE1234F', // Format: 5 letters + 4 digits + 1 letter
                'aadhaar_number' => '123456789012', // Exactly 12 digits

                // **JOB APPLICATION DETAILS**
                'guardian_name' => 'Robert Sharma',
                'education_qualification' => 'B.Tech Computer Science',
                'skills' => 'Laravel,Vue.js,MySQL,REST API',
                'work_experience' => '3 years backend development experience',
                'referee_name' => 'Jane Smith',
                'referee_mobile' => '9998887776',

                // **PAYMENT INFORMATION (If applicable)**
                'payment_status' => 'yes', // yes or no
                'payment_amount' => 500,
                'transaction_id' => 'TXN123456',

                // **VERIFICATION SETTINGS (Default: yes)**
                'verify_email' => 'yes', // yes/no - Mark email as verified
                'verify_mobile' => 'yes', // yes/no - Mark mobile as verified
                'activate_account' => 'yes', // yes/no - Activate account immediately
            ],
        ];
    }

    /**
     * Get Excel columns with sample data for ExcelImportAction.
     * Returns array format: [ [col1, col2, ...], [row1_val1, row1_val2, ...] ]
     */
    public static function getExcelColumns(): array
    {
        $sampleData = self::getSampleData()[0];

        // Convert to array of values for ExcelImportAction
        return [
            array_keys($sampleData), // Header row (column names)
            array_values($sampleData), // Data row (sample values)
        ];
    }
}
