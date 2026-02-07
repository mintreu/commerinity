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
        // Optional columns
        ...self::REQUIRED_HEADERS,
        ...self::OPTIONAL_HEADERS,
    ];

    /**
     * Required columns for import
     */
    public const REQUIRED_HEADERS = [
        'name',
        'email',
        'mobile',
        'job_posting_slug',
        'street_address',
        'city',
        'pin_code',
        'state_name',
        'block_name',
    ];

    /**
     * Optional columns for import
     */
    public const OPTIONAL_HEADERS = [
        'gender',
        'date_of_birth',
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
    ];

    /**
     * Mapping from user-friendly name to internal database column name
     */
    public const HEADER_MAP = [
        'name' => 'name',
        'email' => 'email',
        'mobile' => 'mobile',
        'job_posting_slug' => 'recruitment_slug',
        'street_address' => 'addr_line1',
        'city' => 'city',
        'pin_code' => 'postal_code',
        'state_name' => 'state',
        'block_name' => 'block',
        'country_name' => 'country',
        'address_category' => 'address_type',
        'gender' => 'gender',
        'date_of_birth' => 'dob',
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
    ];

    /**
     * Default values for optional fields
     */
    public const OPTIONAL_DEFAULTS = [
        'payment_status' => 'no',
        'country_name' => 'India',
        'address_category' => 'home',
    ];

    /**
     * Validation rules for each column
     */
    public const VALIDATION_RULES = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'mobile' => 'required|digits:10|unique:users,mobile',
        'job_posting_slug' => 'required|string|exists:recruitments,slug',
        'street_address' => 'required|string',
        'city' => 'required|string',
        'pin_code' => 'required|string',
        'state_name' => 'required|string',
        'block_name' => 'required|string',
        'country_name' => 'nullable|string',
        'address_category' => 'nullable|in:home,work,delivery,pickup,hub,warehouse,service_point,other,present,permanent,business',
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
            'name' => [
                'description' => 'Full name of the applicant',
                'required' => true,
                'example' => 'Rahul Sharma',
            ],
            'email' => [
                'description' => 'Valid email address (must be unique)',
                'required' => true,
                'example' => 'rahul.sharma@example.com',
            ],
            'mobile' => [
                'description' => '10-digit mobile number (must be unique)',
                'required' => true,
                'example' => '9876543210',
            ],
            'job_posting_slug' => [
                'description' => 'Slug of the recruitment/job posting (use the URL segment)',
                'required' => true,
                'example' => 'software-developer-laravel',
            ],
            'block_name' => [
                'description' => 'Block name (must exist in system)',
                'required' => true,
                'example' => 'Sadar',
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
                'name' => 'Rahul Sharma',
                'email' => 'rahul.sharma@example.com',
                'mobile' => '9876543210',
                'job_posting_slug' => 'software-developer-laravel',
                'street_address' => 'Street 12, ABC Nagar, Near City Mall',
                'city' => 'Delhi',
                'pin_code' => '110001',
                'state_name' => 'Delhi', // Full state name
                'block_name' => 'Sadar', // Block name
                // Country and address type are auto-set internally

                // **OPTIONAL FIELDS - You can leave these blank if not needed**
                'gender' => 'male', // male, female, other
                'date_of_birth' => '1998-05-21',
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

                // **VERIFICATION SETTINGS (Default: yes)**
                // No column required; system auto-verifies email/mobile & activates account
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
