<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\RecruitmentRoleCast;
use App\Casts\RecruitmentStatusCast;
use App\Casts\RecruitmentTypeCast;
use App\Models\Recruitment\Recruitment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecruitmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding recruitment positions...');

        $recruitments = [
            // ==========================================
            // ADVISOR POSITIONS (5)
            // ==========================================
            [
                'title' => 'Financial Advisor',
                'description' => "We are looking for experienced Financial Advisors to join our growing team. As a Financial Advisor, you will help clients achieve their financial goals through personalized advice and solutions.\n\nYou will be responsible for assessing clients' financial needs, developing comprehensive financial plans, and recommending appropriate investment products and strategies.",
                'role' => RecruitmentRoleCast::Advisor,
                'location' => 'Mumbai, Maharashtra',
                'employment_type' => RecruitmentTypeCast::FullTime,
                'vacancy' => 5,
                'is_payable' => false,
                'fees' => 0,
                'requirements' => [
                    'Minimum graduation in Commerce/Finance',
                    '2+ years experience in financial services',
                    'Excellent communication skills in English and Hindi',
                    'Strong analytical abilities',
                    'NISM/AMFI certification preferred',
                ],
                'benefits' => [
                    'Competitive base salary + commission',
                    'Health insurance coverage',
                    'Performance bonuses',
                    'Career growth opportunities',
                    'Annual retreat and team outings',
                ],
                'eligibility' => ['min_age' => 21, 'max_age' => 40],
                'status' => RecruitmentStatusCast::Published,
            ],
            [
                'title' => 'Wealth Management Advisor',
                'description' => "Join our wealth management team to provide premium financial advisory services to high-net-worth individuals.\n\nYou will manage client portfolios, provide investment recommendations, and build long-term relationships with affluent clients.",
                'role' => RecruitmentRoleCast::Advisor,
                'location' => 'Kolkata',
                'employment_type' => RecruitmentTypeCast::FullTime,
                'vacancy' => 100,
                'is_payable' => true,
                'fees' => 500, // 5 INR
                'requirements' => [
                    'MBA/CA/CFA preferred',
                    '5+ years in wealth management',
                    'Strong client relationship skills',
                    'Knowledge of investment products',
                    'Proven track record in HNI client handling',
                ],
                'benefits' => [
                    'Premium salary package (15-25 LPA)',
                    'Commission on AUM',
                    'Company car allowance',
                    'International training opportunities',
                    'Stock options',
                ],
                'eligibility' => ['min_age' => 25, 'max_age' => 45],
                'status' => RecruitmentStatusCast::Published,
            ],
            [
                'title' => 'Insurance Advisor',
                'description' => "Looking for motivated Insurance Advisors to join our life insurance division. Help families secure their financial future with the right insurance solutions.\n\nProvide guidance on term plans, endowment policies, ULIPs, and health insurance products.",
                'role' => RecruitmentRoleCast::Advisor,
                'location' => 'Hyderabad, Telangana',
                'employment_type' => RecruitmentTypeCast::FullTime,
                'vacancy' => 8,
                'is_payable' => false,
                'fees' => 0,
                'requirements' => [
                    'Graduate in any discipline',
                    'IRDAI license mandatory',
                    'Good interpersonal skills',
                    'Understanding of insurance products',
                ],
                'benefits' => [
                    'Unlimited earning potential',
                    'Renewals and trail commission',
                    'Flexible working hours',
                    'Training and certification support',
                ],
                'eligibility' => ['min_age' => 21, 'max_age' => 50],
                'status' => RecruitmentStatusCast::Published,
            ],
//            [
//                'title' => 'Mutual Fund Advisor',
//                'description' => 'Help investors build wealth through systematic mutual fund investments. Guide clients in selecting the right funds based on their risk profile and goals.',
//                'role' => RecruitmentRoleCast::Advisor,
//                'location' => 'Bangalore, Karnataka',
//                'employment_type' => RecruitmentTypeCast::PartTime,
//                'vacancy' => 15,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'AMFI/NISM certification required',
//                    'Understanding of equity and debt markets',
//                    'Good mathematical aptitude',
//                    'Sales experience preferred',
//                ],
//                'benefits' => [
//                    'High commission structure',
//                    'Flexible timing',
//                    'Work from anywhere',
//                    'Performance incentives',
//                ],
//                'eligibility' => ['min_age' => 18, 'max_age' => 55],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Senior Investment Advisor',
//                'description' => 'Lead our investment advisory team and mentor junior advisors. Handle key client accounts and develop investment strategies for institutional clients.',
//                'role' => RecruitmentRoleCast::Advisor,
//                'location' => 'Pune, Maharashtra',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 2,
//                'is_payable' => true,
//                'fees' => 149900, // 1499 INR
//                'requirements' => [
//                    'CFA/CFP certification mandatory',
//                    '8+ years investment advisory experience',
//                    'Team management experience',
//                    'Strong market knowledge',
//                    'Client portfolio of 50+ HNI clients',
//                ],
//                'benefits' => [
//                    'CTC 25-40 LPA',
//                    'Leadership role',
//                    'Equity participation',
//                    'Premium health coverage',
//                    'Annual international conferences',
//                ],
//                'eligibility' => ['min_age' => 30, 'max_age' => 50],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//
//            // ==========================================
//            // TRAINER POSITIONS (4)
//            // ==========================================
//            [
//                'title' => 'Sales Trainer',
//                'description' => 'We need dynamic Sales Trainers to train and develop our field sales team across India. Create and deliver impactful training programs that boost sales performance.',
//                'role' => RecruitmentRoleCast::Trainer,
//                'location' => 'Bangalore, Karnataka',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 2,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Minimum 3 years training experience',
//                    'Background in direct sales',
//                    'Excellent presentation skills',
//                    'Willingness to travel 50%',
//                    'Train-the-trainer certification preferred',
//                ],
//                'benefits' => [
//                    'Attractive salary (8-15 LPA)',
//                    'Travel allowance',
//                    'Training certifications',
//                    'Leadership role',
//                ],
//                'eligibility' => ['min_age' => 25, 'max_age' => 45],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Product Trainer',
//                'description' => 'Educate team members about our financial products and services. Create comprehensive training materials and conduct both online and offline workshops.',
//                'role' => RecruitmentRoleCast::Trainer,
//                'location' => 'Hybrid - Pan India',
//                'employment_type' => RecruitmentTypeCast::Contractual,
//                'vacancy' => 5,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Financial services background',
//                    'Training/teaching experience',
//                    'Content development skills',
//                    'Tech-savvy for online training',
//                    'Fluent in Hindi and English',
//                ],
//                'benefits' => [
//                    'Flexible work arrangement',
//                    'Per-session payment (2000-5000/session)',
//                    'Work from home option',
//                    'Professional development',
//                ],
//                'eligibility' => ['min_age' => 23, 'max_age' => 50],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Soft Skills Trainer',
//                'description' => 'Develop and deliver soft skills training programs including communication, leadership, time management, and interpersonal skills for our workforce.',
//                'role' => RecruitmentRoleCast::Trainer,
//                'location' => 'Chennai, Tamil Nadu',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 1,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Masters in Psychology/HR preferred',
//                    '5+ years corporate training experience',
//                    'Certification in behavioral training',
//                    'Excellent facilitation skills',
//                ],
//                'benefits' => [
//                    'Competitive salary',
//                    'Professional development budget',
//                    'Flexible hours',
//                    'International exposure',
//                ],
//                'eligibility' => ['min_age' => 28, 'max_age' => 45],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Technical Trainer - Fintech',
//                'description' => 'Train our teams on fintech platforms, CRM systems, and digital tools. Ensure smooth adoption of technology across the organization.',
//                'role' => RecruitmentRoleCast::Trainer,
//                'location' => 'Noida, Uttar Pradesh',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 2,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'B.Tech/MCA or equivalent',
//                    'Experience with financial software',
//                    'Strong technical documentation skills',
//                    'Patient and good communicator',
//                ],
//                'benefits' => [
//                    'Tech industry salary standards',
//                    'Latest gadgets provided',
//                    'Continuous learning opportunities',
//                    'Stock options',
//                ],
//                'eligibility' => ['min_age' => 24, 'max_age' => 40],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//
//            // ==========================================
//            // SUPPORT POSITIONS (3)
//            // ==========================================
//            [
//                'title' => 'Customer Support Executive',
//                'description' => 'Provide excellent customer service through phone, email, and chat. Resolve customer queries and complaints efficiently while maintaining high satisfaction scores.',
//                'role' => RecruitmentRoleCast::Support,
//                'location' => 'Chennai, Tamil Nadu',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 10,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Graduate in any discipline',
//                    'Excellent communication skills',
//                    'Basic computer knowledge',
//                    'Customer service orientation',
//                    'Typing speed 30+ WPM',
//                ],
//                'benefits' => [
//                    'Fixed salary + incentives (3-5 LPA)',
//                    'Rotational shifts with shift allowance',
//                    'Medical benefits',
//                    'Career progression to Team Lead',
//                ],
//                'eligibility' => ['min_age' => 18, 'max_age' => 35],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Senior Customer Support Specialist',
//                'description' => 'Handle escalated customer issues and complex queries. Mentor junior support staff and help improve support processes.',
//                'role' => RecruitmentRoleCast::Support,
//                'location' => 'Gurugram, Haryana',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 3,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    '3+ years customer support experience',
//                    'Experience handling escalations',
//                    'Team mentoring experience',
//                    'Knowledge of CRM tools',
//                    'Problem-solving mindset',
//                ],
//                'benefits' => [
//                    'Salary 5-8 LPA',
//                    'Team lead opportunities',
//                    'Certifications sponsored',
//                    'Work from home 2 days/week',
//                ],
//                'eligibility' => ['min_age' => 23, 'max_age' => 40],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Technical Support Engineer',
//                'description' => 'Provide technical support for our mobile app and web platform users. Troubleshoot issues and coordinate with the development team for bug fixes.',
//                'role' => RecruitmentRoleCast::Support,
//                'location' => 'Bangalore, Karnataka',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 4,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'B.Tech/BCA or equivalent',
//                    'Understanding of web/mobile technologies',
//                    'SQL basics',
//                    'Good analytical skills',
//                    'Experience with ticketing systems',
//                ],
//                'benefits' => [
//                    'Tech salary standards (4-7 LPA)',
//                    'Flexible timing',
//                    'Technical training',
//                    'Path to development roles',
//                ],
//                'eligibility' => ['min_age' => 21, 'max_age' => 35],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//
//            // ==========================================
//            // EXECUTIVE POSITIONS (5)
//            // ==========================================
//            [
//                'title' => 'Digital Marketing Executive',
//                'description' => 'Drive digital marketing campaigns across social media, email, and paid advertising channels. Analyze campaign performance and optimize for better ROI.',
//                'role' => RecruitmentRoleCast::Executive,
//                'location' => 'Pune, Maharashtra',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 2,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'BBA/MBA in Marketing',
//                    '1+ years digital marketing experience',
//                    'Knowledge of SEO, SEM, Social Media',
//                    'Google Analytics certification',
//                    'Creative thinking',
//                ],
//                'benefits' => [
//                    'Competitive salary (4-8 LPA)',
//                    'Performance bonus',
//                    'Learning opportunities',
//                    'Young dynamic team',
//                ],
//                'eligibility' => ['min_age' => 21, 'max_age' => 35],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Field Sales Executive',
//                'description' => 'Generate leads and convert prospects into customers through field sales activities. Build relationships with local businesses and individuals.',
//                'role' => RecruitmentRoleCast::Executive,
//                'location' => 'Multiple Cities - Pan India',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 50,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Minimum 12th pass',
//                    'Sales experience preferred but not mandatory',
//                    'Own two-wheeler with valid license',
//                    'Local language proficiency',
//                    'Smartphone with internet',
//                ],
//                'benefits' => [
//                    'Base salary + high commission',
//                    'Fuel allowance',
//                    'Mobile allowance',
//                    'Quick promotions based on performance',
//                    'Monthly contests and rewards',
//                ],
//                'eligibility' => ['min_age' => 18, 'max_age' => 40],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Business Development Executive',
//                'description' => 'Identify and develop new business opportunities. Build partnerships with corporates, institutions, and channel partners.',
//                'role' => RecruitmentRoleCast::Executive,
//                'location' => 'Mumbai, Maharashtra',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 3,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'MBA preferred',
//                    '2+ years B2B sales experience',
//                    'Strong networking skills',
//                    'Presentation skills',
//                    'Corporate communication etiquette',
//                ],
//                'benefits' => [
//                    'Salary 6-12 LPA + incentives',
//                    'Travel allowance',
//                    'Laptop provided',
//                    'Fast track to management',
//                ],
//                'eligibility' => ['min_age' => 24, 'max_age' => 38],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Operations Executive',
//                'description' => 'Manage day-to-day operations including order processing, documentation, and coordination with various departments.',
//                'role' => RecruitmentRoleCast::Executive,
//                'location' => 'Kolkata, West Bengal',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 4,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Graduate in any discipline',
//                    'Proficient in MS Office',
//                    'Attention to detail',
//                    'Good organizational skills',
//                    'Multi-tasking ability',
//                ],
//                'benefits' => [
//                    'Stable salary (3-5 LPA)',
//                    'Regular working hours',
//                    'Mediclaim',
//                    'Annual increments',
//                ],
//                'eligibility' => ['min_age' => 20, 'max_age' => 35],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'HR Executive',
//                'description' => 'Support recruitment, onboarding, and employee engagement activities. Maintain HR records and assist in payroll processing.',
//                'role' => RecruitmentRoleCast::Executive,
//                'location' => 'Ahmedabad, Gujarat',
//                'employment_type' => RecruitmentTypeCast::FullTime,
//                'vacancy' => 2,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'MBA in HR or equivalent',
//                    '1+ years HR experience',
//                    'Knowledge of labor laws',
//                    'Good interpersonal skills',
//                    'HRMS software experience',
//                ],
//                'benefits' => [
//                    'Competitive salary',
//                    'Growth to HR Manager',
//                    'Training opportunities',
//                    'Great work culture',
//                ],
//                'eligibility' => ['min_age' => 22, 'max_age' => 35],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//
//            // ==========================================
//            // INTERN POSITIONS (4) - Can become staff after approval
//            // ==========================================
//            [
//                'title' => 'Marketing Intern',
//                'description' => "Learn and assist in marketing activities including content creation, social media management, and campaign execution. Great opportunity for fresh graduates.\n\nThis is a stepping stone to a full-time Marketing Executive role. Top performers will be converted to permanent staff.",
//                'role' => RecruitmentRoleCast::Intern,
//                'location' => 'Remote / Work from Home',
//                'employment_type' => RecruitmentTypeCast::Internship,
//                'vacancy' => 10,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Currently pursuing or recently completed graduation',
//                    'Interest in marketing',
//                    'Basic design skills (Canva)',
//                    'Good written communication',
//                    'Active on social media',
//                ],
//                'benefits' => [
//                    'Stipend 8000-12000/month',
//                    'Certificate on completion',
//                    'Letter of recommendation',
//                    'PPO for top performers',
//                    'Flexible hours',
//                    'Path to full-time role',
//                ],
//                'eligibility' => ['min_age' => 18, 'max_age' => 25],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Finance Intern',
//                'description' => "Get hands-on experience in financial analysis, reporting, and investment research. Ideal for commerce and finance students.\n\nSuccessful interns can be promoted to Junior Financial Advisor positions.",
//                'role' => RecruitmentRoleCast::Intern,
//                'location' => 'Delhi NCR',
//                'employment_type' => RecruitmentTypeCast::Internship,
//                'vacancy' => 5,
//                'is_payable' => true,
//                'fees' => 29900, // 299 INR registration fee
//                'requirements' => [
//                    'B.Com/BBA/MBA Finance student',
//                    'Strong Excel skills',
//                    'Interest in financial markets',
//                    'Analytical mindset',
//                    'Available for 3-6 months',
//                ],
//                'benefits' => [
//                    'Stipend 10000-15000/month',
//                    'Industry exposure',
//                    'Mentorship from senior analysts',
//                    'Job offer for performers',
//                    'Direct path to Advisor role',
//                ],
//                'eligibility' => ['min_age' => 18, 'max_age' => 26],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'Sales Intern',
//                'description' => "Start your sales career with hands-on field experience. Learn sales techniques, customer handling, and business development skills.\n\nTop performers will be offered permanent Field Sales Executive positions.",
//                'role' => RecruitmentRoleCast::Intern,
//                'location' => 'Multiple Cities - Pan India',
//                'employment_type' => RecruitmentTypeCast::Internship,
//                'vacancy' => 25,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'Currently pursuing graduation (any stream)',
//                    'Good communication skills',
//                    'Willingness to do field work',
//                    'Basic smartphone required',
//                    'Self-motivated attitude',
//                ],
//                'benefits' => [
//                    'Stipend 6000-10000/month + incentives',
//                    'Sales training certification',
//                    'Field allowance',
//                    'Performance-based bonuses',
//                    'Fast-track to permanent role',
//                ],
//                'eligibility' => ['min_age' => 18, 'max_age' => 25],
//                'status' => RecruitmentStatusCast::Published,
//            ],
//            [
//                'title' => 'HR & Operations Intern',
//                'description' => "Learn HR operations including recruitment support, employee onboarding, documentation, and office administration.\n\nExcellent opportunity to build a career in Human Resources.",
//                'role' => RecruitmentRoleCast::Intern,
//                'location' => 'Mumbai, Maharashtra',
//                'employment_type' => RecruitmentTypeCast::Internship,
//                'vacancy' => 3,
//                'is_payable' => false,
//                'fees' => 0,
//                'requirements' => [
//                    'BBA/MBA (HR) student or fresh graduate',
//                    'Good organizational skills',
//                    'Proficiency in MS Office',
//                    'Excellent communication',
//                    'Attention to detail',
//                ],
//                'benefits' => [
//                    'Stipend 8000-12000/month',
//                    'Real HR project exposure',
//                    'Mentorship from HR professionals',
//                    'Certificate and recommendation',
//                    'Possibility of HR Executive role',
//                ],
//                'eligibility' => ['min_age' => 18, 'max_age' => 26],
//                'status' => RecruitmentStatusCast::Published,
//            ],

            // ==========================================
            // DRAFT & CLOSED (for testing different states)
            // ==========================================
            [
                'title' => 'Regional Manager (Coming Soon)',
                'description' => 'Regional management position - details being finalized.',
                'role' => RecruitmentRoleCast::Advisor,
                'location' => 'Multiple Locations',
                'employment_type' => RecruitmentTypeCast::FullTime,
                'vacancy' => 5,
                'is_payable' => true,
                'fees' => 199900,
                'requirements' => ['To be announced'],
                'benefits' => ['To be announced'],
                'eligibility' => ['min_age' => 30, 'max_age' => 50],
                'status' => RecruitmentStatusCast::Draft,
            ],
            [
                'title' => 'Branch Manager - Kolkata (Closed)',
                'description' => 'Branch management position that has been filled.',
                'role' => RecruitmentRoleCast::Advisor,
                'location' => 'Kolkata, West Bengal',
                'employment_type' => RecruitmentTypeCast::FullTime,
                'vacancy' => 0,
                'is_payable' => true,
                'fees' => 199900,
                'requirements' => ['Position has been filled'],
                'benefits' => ['Position has been filled'],
                'eligibility' => ['min_age' => 28, 'max_age' => 45],
                'status' => RecruitmentStatusCast::Closed,
            ],
        ];

        $count = 0;
        foreach ($recruitments as $data) {
            $title = $data['title'];

            Recruitment::updateOrCreate(
                ['title' => $title],
                array_merge($data, [
                    'uuid' => Str::uuid()->toString(),
                    'slug' => Str::slug($title).'-'.Str::random(6),
                    'open_date' => $data['status'] === RecruitmentStatusCast::Published ? now()->subDays(rand(1, 30)) : null,
                    'close_date' => $data['status'] === RecruitmentStatusCast::Published ? now()->addDays(rand(30, 90)) : null,
                ])
            );
            $count++;
        }

        $this->command->info("Seeded {$count} recruitment positions.");

        Recruitment::factory(20)->create();

        $this->command->info("Seeded 20  recruitment positions via factory.");

    }
}

