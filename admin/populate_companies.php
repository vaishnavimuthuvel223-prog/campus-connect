<?php
@session_start();
require_once __DIR__ . '/../config/db.php';

// Companies data with real information
$companies = [
    [
        'name' => 'Amazon',
        'package' => '18 LPA',
        'description' => 'Amazon is one of the world\'s most innovative companies, operating global e-commerce, cloud computing, AI, and digital streaming services. Known for customer obsession and leadership principles.',
        'location' => 'Seattle, Washington, USA',
        'founded' => 1994,
        'ceo' => 'Andy Jassy',
        'branches' => 'Seattle, Mumbai, Bangalore, Pune, Hyderabad, Delhi',
        'skills' => 'Java, Python, AWS, SQL, Problem Solving, System Design'
    ],
    [
        'name' => 'Google',
        'package' => '25 LPA',
        'description' => 'Google is a leading technology company specializing in search, cloud computing, AI/ML, and digital advertising. Famous for innovation and engineering excellence.',
        'location' => 'Mountain View, California, USA',
        'founded' => 1998,
        'ceo' => 'Sundar Pichai',
        'branches' => 'Mountain View, New York, London, Bangalore, Hyderabad, Pune',
        'skills' => 'C++, Python, JavaScript, Data Structures, Algorithms, Machine Learning'
    ],
    [
        'name' => 'Infosys',
        'package' => '8 LPA',
        'description' => 'Infosys is a global leader in digital services and consulting, delivering IT solutions and transformation services to businesses worldwide since 1981.',
        'location' => 'Bangalore, Karnataka, India',
        'founded' => 1981,
        'ceo' => 'Salil Parekh',
        'branches' => 'Bangalore, Hyderabad, Pune, Noida, Mumbai, Chennai, Kolkata',
        'skills' => 'Java, Python, Cloud Services, SAP, COBOL, Microservices, Docker'
    ],
    [
        'name' => 'TCS',
        'package' => '7.5 LPA',
        'description' => 'Tata Consultancy Services is an Indian multinational IT services company providing IT services, consulting, and business solutions across 150+ countries.',
        'location' => 'Mumbai, Maharashtra, India',
        'founded' => 1968,
        'ceo' => 'K. Krithivasan',
        'branches' => 'Mumbai, Bangalore, Hyderabad, Delhi, Pune, Chennai, Kolkata, Ahmedabad',
        'skills' => 'Java, Python, .NET, Mainframe, Cloud, Cybersecurity, Analytics'
    ],
    [
        'name' => 'Wipro',
        'package' => '7 LPA',
        'description' => 'Wipro is a leading global information technology, consulting, and business process services company serving clients across 167+ countries.',
        'location' => 'Bangalore, Karnataka, India',
        'founded' => 1980,
        'ceo' => 'Thierry Delaporte',
        'branches' => 'Bangalore, Hyderabad, Pune, Chennai, Kolkata, Delhi, Mumbai, Noida',
        'skills' => 'Java, Python, Cloud Computing, Cybersecurity, Digital, AI/ML'
    ],
    [
        'name' => 'Cognizant',
        'package' => '8.5 LPA',
        'description' => 'Cognizant Technology Solutions is an American multinational IT services and consulting company providing digital transformation and IT solutions.',
        'location' => 'New Jersey, USA (Bangalore, India operations)',
        'founded' => 1994,
        'ceo' => 'Ravi Kumar S.',
        'branches' => 'New Jersey, Mumbai, Bangalore, Hyderabad, Pune, Chennai, Kolkata',
        'skills' => 'Java, Python, Cloud, DevOps, Salesforce, Digital Transformation, RPA'
    ]
];

$success_count = 0;
$error_count = 0;

foreach ($companies as $c) {
    try {
        // Check if company exists
        $check = $conn->prepare("SELECT company_id FROM companies WHERE company_name = ?");
        $check->execute([$c['name']]);
        $existing = $check->fetch();
        
        if ($existing) {
            // Update existing company
            $stmt = $conn->prepare("
                UPDATE companies 
                SET package = ?, description = ?, location = ?, founded = ?, ceo = ?, branches = ?, skills = ?
                WHERE company_name = ?
            ");
            $stmt->execute([
                $c['package'],
                $c['description'],
                $c['location'],
                $c['founded'],
                $c['ceo'],
                $c['branches'],
                $c['skills'],
                $c['name']
            ]);
            echo "✅ Updated: {$c['name']}<br>";
            $success_count++;
        } else {
            // Insert new company
            $stmt = $conn->prepare("
                INSERT INTO companies (company_name, package, description, location, founded, ceo, branches, skills)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $c['name'],
                $c['package'],
                $c['description'],
                $c['location'],
                $c['founded'],
                $c['ceo'],
                $c['branches'],
                $c['skills']
            ]);
            echo "✅ Added: {$c['name']}<br>";
            $success_count++;
        }
    } catch (Exception $e) {
        echo "❌ Error with {$c['name']}: " . $e->getMessage() . "<br>";
        $error_count++;
    }
}

echo "<br><strong>Summary:</strong> $success_count succeeded, $error_count failed.<br>";
echo "<br><a href='companies.php'>← Back to Companies</a>";
?>
