<?php

declare(strict_types=1);

namespace Tests\Feature\Imports;

use App\Filament\Resources\JobApplications\Schemas\ImportSchema;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * Factory for generating realistic job application import data.
 * Creates 1000+ entries with Faker for testing.
 */
final class JobApplicationImportFactory
{
    private Generator $faker;

    private array $states = [
        ['code' => 'AP', 'name' => 'Andhra Pradesh'],
        ['code' => 'AR', 'name' => 'Arunachal Pradesh'],
        ['code' => 'AS', 'name' => 'Assam'],
        ['code' => 'BR', 'name' => 'Bihar'],
        ['code' => 'CT', 'name' => 'Chhattisgarh'],
        ['code' => 'GA', 'name' => 'Goa'],
        ['code' => 'GJ', 'name' => 'Gujarat'],
        ['code' => 'HR', 'name' => 'Haryana'],
        ['code' => 'HP', 'name' => 'Himachal Pradesh'],
        ['code' => 'JK', 'name' => 'Jammu and Kashmir'],
        ['code' => 'JH', 'name' => 'Jharkhand'],
        ['code' => 'KA', 'name' => 'Karnataka'],
        ['code' => 'KL', 'name' => 'Kerala'],
        ['code' => 'MP', 'name' => 'Madhya Pradesh'],
        ['code' => 'MH', 'name' => 'Maharashtra'],
        ['code' => 'MN', 'name' => 'Manipur'],
        ['code' => 'ML', 'name' => 'Meghalaya'],
        ['code' => 'MZ', 'name' => 'Mizoram'],
        ['code' => 'NL', 'name' => 'Nagaland'],
        ['code' => 'OR', 'name' => 'Odisha'],
        ['code' => 'PB', 'name' => 'Punjab'],
        ['code' => 'RJ', 'name' => 'Rajasthan'],
        ['code' => 'SK', 'name' => 'Sikkim'],
        ['code' => 'TN', 'name' => 'Tamil Nadu'],
        ['code' => 'TG', 'name' => 'Telangana'],
        ['code' => 'TR', 'name' => 'Tripura'],
        ['code' => 'UP', 'name' => 'Uttar Pradesh'],
        ['code' => 'UT', 'name' => 'Uttarakhand'],
        ['code' => 'WB', 'name' => 'West Bengal'],
        ['code' => 'DL', 'name' => 'Delhi'],
        ['code' => 'PY', 'name' => 'Puducherry'],
    ];

    private array $cities = [
        'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Ahmedabad', 'Chennai', 'Kolkata', 'Pune', 'Jaipur', 'Lucknow',
        'Kanpur', 'Nagpur', 'Indore', 'Thane', 'Bhopal', 'Visakhapatnam', 'Pimpri-Chinchwad', 'Patna', 'Vadodara',
        'Ghaziabad', 'Ludhiana', 'Agra', 'Nashik', 'Faridabad', 'Meerut', 'Rajkot', 'Kalyan-Dombivli',
        'Vasai-Virar', 'Varanasi', 'Srinagar', 'Aurangabad', 'Dhanbad', 'Amritsar', 'Navi Mumbai', 'Allahabad',
        'Ranchi', 'Howrah', 'Coimbatore', 'Jabalpur', 'Gwalior', 'Vijayawada', 'Jodhpur', 'Madurai', 'Raipur',
    ];

    private array $skills = [
        'Laravel', 'PHP', 'JavaScript', 'Vue.js', 'React', 'Node.js', 'Python', 'Django', 'Flask',
        'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'AWS', 'Docker', 'Kubernetes', 'Git', 'CI/CD',
        'HTML', 'CSS', 'Tailwind CSS', 'Bootstrap', 'jQuery', 'TypeScript', 'Angular', 'Express.js',
        'Java', 'Spring Boot', 'Hibernate', 'REST API', 'GraphQL', 'Microservices', 'DevOps',
        'Machine Learning', 'Data Science', 'React Native', 'Flutter', 'Android', 'iOS',
    ];

    private array $educations = [
        'B.Tech Computer Science', 'B.Tech Information Technology', 'B.Tech Electronics',
        'B.Tech Mechanical', 'B.Tech Civil', 'B.Tech Electrical', 'MCA', 'BCA', 'B.Sc Computer Science',
        'M.Tech Computer Science', 'MBA', 'BBA', 'B.Com', 'M.Com', 'BA', 'MA', 'PhD',
    ];

    private array $genders = ['male', 'female', 'other'];

    public function __construct()
    {
        $this->faker = FakerFactory::create('en_IN');
        $this->faker->seed(12345); // For reproducible results
    }

    /**
     * Generate header row with comments for Excel
     */
    public function generateHeaderRow(): array
    {
        return ImportSchema::HEADERS;
    }

    /**
     * Generate a single realistic row
     */
    public function generateRow(int $index, array $options = []): array
    {
        $state = $this->faker->randomElement($this->states);
        $city = $this->faker->randomElement($this->cities);
        $mobile = '9'.$this->faker->numerify('#########'); // 10 digits starting with 9
        $email = $this->faker->unique()->safeEmail();

        // Generate password-friendly DOB
        $dob = $this->faker->dateTimeBetween('-35 years', '-18 years')->format('Y-m-d');
        $pan = $this->generatePan();
        $aadhaar = $this->faker->numerify('############'); // 12 digits

        return [
            // Required fields
            'name' => $this->faker->name(),
            'email' => $email,
            'mobile' => $mobile,
            'job_posting_slug' => $options['recruitment_slug'] ?? 'software-developer-position',

            // Address
            'street_address' => $this->faker->streetAddress(),
            'city' => $city,
            'pin_code' => $this->faker->numerify('######'), // 6 digits for India
            'state_name' => $state['name'],
            'block_name' => $options['block_name'] ?? 'Sadar',
            // Country and address type are auto-set internally

            // Optional profile
            'gender' => $this->faker->randomElement($this->genders),
            'date_of_birth' => $dob,

            // KYC (80% have KYC data)
            'pan_number' => $this->faker->boolean(80) ? $pan : null,
            'aadhaar_number' => $this->faker->boolean(80) ? $aadhaar : null,

            // Job application (70% have details)
            'guardian_name' => $this->faker->boolean(70) ? $this->faker->name('male') : null,
            'education_qualification' => $this->faker->boolean(70) ? $this->faker->randomElement($this->educations) : null,
            'skills' => $this->faker->boolean(70) ? $this->generateSkills() : null,
            'work_experience' => $this->faker->boolean(70) ? $this->generateExperience() : null,
            'referee_name' => $this->faker->boolean(50) ? $this->faker->name() : null,
            'referee_mobile' => $this->faker->boolean(50) ? '9'.$this->faker->numerify('#########') : null,

            // Payment (30% are paid)
            'payment_status' => $this->faker->boolean(30) ? 'yes' : 'no',
            'payment_amount' => $this->faker->boolean(30) ? $this->faker->numberBetween(500, 2000) : null,
        ];
    }

    /**
     * Generate multiple rows (e.g., 1000 entries)
     */
    public function generateRows(int $count, array $options = []): array
    {
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $rows[] = $this->generateRow($i + 1, $options);
        }

        return $rows;
    }

    /**
     * Generate valid PAN number (format: ABCDE1234F)
     */
    private function generatePan(): string
    {
        $letters = strtoupper($this->faker->lexify('?????')); // 5 letters
        $numbers = $this->faker->numerify('####'); // 4 digits
        $lastLetter = strtoupper($this->faker->lexify('?')); // 1 letter

        return $letters.$numbers.$lastLetter;
    }

    /**
     * Generate comma-separated skills (2-5 skills)
     */
    private function generateSkills(): string
    {
        $count = $this->faker->numberBetween(2, 5);
        $skills = $this->faker->randomElements($this->skills, $count);

        return implode(',', $skills);
    }

    /**
     * Generate experience description
     */
    private function generateExperience(): string
    {
        $years = $this->faker->numberBetween(1, 8);
        $area = $this->faker->randomElement([
            'backend development', 'frontend development', 'full-stack development',
            'mobile app development', 'web development', 'software development',
            'database management', 'cloud architecture', 'DevOps',
        ]);

        return "{$years} years {$area}";
    }

    /**
     * Generate a mix of valid and invalid rows for error testing
     */
    public function generateTestDataset(): array
    {
        $dataset = [];

        // Valid rows
        $dataset[] = $this->generateRow(1);
        $dataset[] = $this->generateRow(2);

        // Row with invalid email
        $row = $this->generateRow(3);
        $row['email'] = 'invalid-email';
        $dataset[] = $row;

        // Row with invalid mobile (9 digits)
        $row = $this->generateRow(4);
        $row['mobile'] = '123456789';
        $dataset[] = $row;

        // Row with invalid PAN
        $row = $this->generateRow(5);
        $row['pan_number'] = 'INVALIDPAN';
        $dataset[] = $row;

        // Row with duplicate email
        $row = $this->generateRow(6);
        $row['email'] = $dataset[0]['email'];
        $dataset[] = $row;

        // Another valid row
        $dataset[] = $this->generateRow(7);

        return $dataset;
    }
}
