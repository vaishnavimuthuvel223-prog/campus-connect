<?php
require_once 'config/db.php';
$companies = $conn->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$students = $conn->query("SELECT COUNT(*) FROM students")->fetchColumn();
$drives = $conn->query("SELECT COUNT(*) FROM drives")->fetchColumn();
$placed = $conn->query("SELECT COUNT(*) FROM applications WHERE final_status='selected'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Recruitment Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: url('/campuss/uploads/clg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #1f2937;
            overflow-x: hidden;
            min-height: 100vh;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-25px) scale(1.05); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
            50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.8); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes rotate360 {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        body { animation: fadeIn 0.8s ease-out; }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 4%;
            background: linear-gradient(135deg, rgba(252, 253, 255, 0.95) 0%, rgba(240, 249, 255, 0.92) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(59, 130, 246, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            animation: slideDown 0.8s ease-out;
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            animation: slideInLeft 0.8s ease-out;
        }

        .logo-img {
            height: 80px;
            width: auto;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(59, 130, 246, 0.1);
            padding: 4px;
            background: linear-gradient(135deg, rgba(255,255,255,0.8), rgba(240, 249, 255, 0.6));
        }

        .logo-img:hover {
            transform: scale(1.12) rotate(5deg);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .logo {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #3b82f6, #2563eb, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.8px;
            animation: slideInLeft 0.8s ease-out 0.1s both;
            text-shadow: 0 2px 10px rgba(59, 130, 246, 0.15);
        }

        nav {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            animation: slideInRight 0.8s ease-out;
        }

        nav a {
            padding: 0.8rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            letter-spacing: 0.3px;
        }

        .nav-btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            box-shadow: 0 6px 25px rgba(59, 130, 246, 0.35);
            position: relative;
            overflow: hidden;
        }

        .nav-btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .nav-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.6);
        }

        .nav-btn-primary:hover::before {
            transform: translateX(100%);
        }

        .nav-btn-secondary {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border-color: #3b82f6;
        }

        .nav-btn-secondary:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.1));
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.2);
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 8rem 4% 4rem;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(2px);
            z-index: 1;
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                        radial-gradient(ellipse 900px 700px at 20% 10%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                        radial-gradient(ellipse 700px 500px at 80% 90%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
            z-index: 2;
        }

        .hero-container {
            max-width: 1200px;
            width: 100%;
            position: relative;
            z-index: 3;
            text-align: center;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .college-name {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            color: #1f2937;
            letter-spacing: -1.5px;
            animation: float 5s ease-in-out infinite;
            text-shadow: 0 2px 15px rgba(255,255,255,0.8), 0 4px 20px rgba(59, 130, 246, 0.2);
            filter: drop-shadow(0 2px 10px rgba(255, 255, 255, 0.6));
        }

        .website-title {
            font-size: 1.4rem;
            color: #000000;
            margin-bottom: 3rem;
            font-weight: 700;
            letter-spacing: 3px;
            animation: slideUp 0.8s ease-out 0.3s both;
            text-shadow: 0 2px 15px rgba(255, 255, 255, 0.4);
            font-style: italic;
        }

        .portal-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }

        .portal-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1.5px solid #e0e7ef;
            border-radius: 22px;
            padding: 2.8rem 2.2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
            box-shadow: 0 10px 36px rgba(0, 0, 0, 0.18);
        }

        /* Student Card - Blue */
        .portal-card:nth-child(1) {
            animation-delay: 0s;
            border-color: rgba(59, 130, 246, 0.2);
            background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(224, 242, 254, 0.5));
        }

        .portal-card:nth-child(1):hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(255,255,255,0.99), rgba(219, 234, 254, 0.7));
            box-shadow: 0 25px 60px rgba(59, 130, 246, 0.35);
            transform: translateY(-12px) scale(1.02);
        }

        /* Staff Card - Purple */
        .portal-card:nth-child(2) {
            animation-delay: 0.15s;
            border-color: rgba(139, 92, 246, 0.2);
            background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(243, 232, 255, 0.5));
        }

        .portal-card:nth-child(2):hover {
            border-color: #8b5cf6;
            background: linear-gradient(135deg, rgba(255,255,255,0.99), rgba(237, 220, 255, 0.7));
            box-shadow: 0 25px 60px rgba(139, 92, 246, 0.35);
            transform: translateY(-12px) scale(1.02);
        }

        /* Admin Card - Red */
        .portal-card:nth-child(3) {
            animation-delay: 0.3s;
            border-color: rgba(220, 38, 38, 0.3);
            background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(254, 226, 226, 0.6));
        }

        .portal-card:nth-child(3):hover {
            border-color: #dc2626;
            background: linear-gradient(135deg, rgba(255,255,255,0.99), rgba(254, 202, 202, 0.8));
            box-shadow: 0 25px 60px rgba(220, 38, 38, 0.35);
            transform: translateY(-12px) scale(1.02);
        }

        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            animation: shimmer 3s infinite;
        }

        .portal-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 50%, rgba(255, 255, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 1;
        }

        .portal-card:hover::after {
            opacity: 1;
        }

        .portal-card:hover {
            transform: translateY(-12px) scale(1.02);
        }

        .portal-name {
            font-size: 2.3rem;
            font-weight: 900;
            margin-bottom: 1.3rem;
            position: relative;
            z-index: 10;
            letter-spacing: -0.5px;
            transition: all 0.3s ease;
            animation: slideUp 0.7s ease-out;
        }

        /* Student - Blue Name */
        .portal-card:nth-child(1) .portal-name {
            color: #2563eb;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .portal-card:nth-child(1):hover .portal-name {
            transform: scale(1.08);
        }

        /* Staff - Purple Name */
        .portal-card:nth-child(2) .portal-name {
            color: #8b5cf6;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .portal-card:nth-child(2):hover .portal-name {
            transform: scale(1.08);
        }

        /* Admin - Red Name */
        .portal-card:nth-child(3) .portal-name {
            color: #dc2626;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.4rem;
            letter-spacing: 0px;
        }

        .portal-card:nth-child(3):hover .portal-name {
            transform: scale(1.08);
        }

        .portal-desc {
            font-size: 1.05rem;
            color: #4b5563;
            margin-bottom: 2.3rem;
            line-height: 1.8;
            position: relative;
            z-index: 10;
            font-weight: 600;
            letter-spacing: 0.02em;
            animation: slideUp 0.7s ease-out 0.1s both;
        }

        .portal-btn {
            display: inline-block;
            width: 100%;
            padding: 1.2rem;
            border: 2.5px solid;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1.05rem;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            z-index: 10;
            overflow: hidden;
            letter-spacing: 0.5px;
            animation: slideUp 0.7s ease-out 0.2s both;
        }

        .portal-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
            z-index: -1;
        }

        .portal-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 12px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        /* Student - Blue Button */
        .portal-card:nth-child(1) .portal-btn {
            background: transparent;
            color: #1e40af;
            border-color: #1e40af;
        }

        .portal-card:nth-child(1) .portal-btn:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            box-shadow: 0 10px 30px rgba(30, 64, 175, 0.5), inset 0 0 20px rgba(255, 255, 255, 0.2);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
            transform: translateY(-3px);
        }

        .portal-card:nth-child(1) .portal-btn:hover::before {
            transform: translateX(100%);
        }

        /* Staff - Purple Button */
        .portal-card:nth-child(2) .portal-btn {
            background: transparent;
            color: #5b21b6;
            border-color: #5b21b6;
        }

        .portal-card:nth-child(2) .portal-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: #fff;
            box-shadow: 0 10px 30px rgba(91, 33, 182, 0.5), inset 0 0 20px rgba(255, 255, 255, 0.2);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
            transform: translateY(-3px);
        }

        .portal-card:nth-child(2) .portal-btn:hover::before {
            transform: translateX(100%);
        }

        /* Admin - Red Button */
        .portal-card:nth-child(3) .portal-btn {
            background: transparent;
            color: #dc2626;
            border-color: #dc2626;
            font-weight: 900;
        }

        .portal-card:nth-child(3) .portal-btn:hover {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: #fff;
            box-shadow: 0 12px 35px rgba(220, 38, 38, 0.5), inset 0 0 20px rgba(255, 255, 255, 0.2);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
            transform: translateY(-3px);
        }

        .portal-card:nth-child(3) .portal-btn:hover::before {
            transform: translateX(100%);
        }

        .portal-btn:hover {
            transform: translateY(-3px);
            letter-spacing: 1px;
        }

        .floating-element {
            position: absolute;
            border-radius: 50%;
            opacity: 0.2;
            z-index: 0;
            filter: blur(40px);
        }

        .float-1 {
            width: 300px;
            height: 300px;
            background: rgba(59, 130, 246, 0.8);
            top: 20%;
            left: 10%;
            animation: floatSlow 8s ease-in-out infinite;
        }

        .float-2 {
            width: 250px;
            height: 250px;
            background: rgba(139, 92, 246, 0.6);
            bottom: 15%;
            right: 10%;
            animation: floatSlow 12s ease-in-out infinite;
        }

        .float-3 {
            width: 200px;
            height: 200px;
            background: rgba(59, 130, 246, 0.5);
            top: 50%;
            right: 5%;
            animation: floatSlow 10s ease-in-out infinite;
        }

        /* Hamburger for index page */
        .hamburger-idx {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 6px;
            border: none;
            background: transparent;
            border-radius: 8px;
        }
        .hamburger-idx span {
            display: block;
            width: 24px;
            height: 3px;
            background: #3b82f6;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        .hamburger-idx.open span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
        .hamburger-idx.open span:nth-child(2) { opacity: 0; }
        .hamburger-idx.open span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

        @media (max-width: 1024px) {
            .college-name { font-size: 2.8rem; }
            .portal-selector { grid-template-columns: repeat(2, 1fr); gap: 1.8rem; }
        }

        @media (max-width: 768px) {
            header { padding: 0.9rem 4%; }
            .logo { font-size: 1.3rem; }
            .hamburger-idx { display: flex; }
            nav { 
                display: none;
                position: absolute;
                top: 100%;
                left: 0; right: 0;
                background: rgba(252, 253, 255, 0.98);
                flex-direction: column;
                padding: 1rem 4%;
                gap: 0.5rem;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                z-index: 999;
            }
            nav.open { display: flex; }
            nav a { width: 100%; text-align: center; }
            .hero { padding: 7rem 4% 2rem; }
            .college-name { font-size: 1.6rem; letter-spacing: -0.5px; }
            .website-title { font-size: 1rem; }
            .portal-selector { grid-template-columns: 1fr; gap: 1.5rem; }
            .portal-card { padding: 2rem 1.5rem; }
            .portal-name { font-size: 1.8rem; }
            .float-1, .float-2, .float-3 { display: none; }
        }

        @media (max-width: 480px) {
            header { padding: 0.8rem 4%; }
            .logo-img { height: 50px; }
            .logo { font-size: 1.15rem; }
            .college-name { font-size: 1.3rem; letter-spacing: 0; }
            .website-title { font-size: 0.9rem; margin-bottom: 2rem; letter-spacing: 1.5px; }
            .portal-card { padding: 1.8rem 1.2rem; }
            .portal-name { font-size: 1.6rem; }
            .portal-desc { font-size: 0.95rem; margin-bottom: 1.5rem; }
            .portal-btn { padding: 0.9rem; font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="floating-element float-1"></div>
    <div class="floating-element float-2"></div>
    <div class="floating-element float-3"></div>

    <header>
        <div class="header-brand">
            <img src="/campuss/uploads/MKCE-Logo.jpg" alt="M Kumarasamy College of Engineering Logo" class="logo-img gsap-animate" data-gsap="scale-in">
            <div class="college-name gsap-animate" data-gsap="fade-up" style="font-size:1.1rem;font-weight:800;line-height:1.2;max-width:220px;white-space:normal;text-align:left;letter-spacing:0.1px;overflow-wrap:break-word;word-break:break-word;background:linear-gradient(135deg,#3b82f6,#2563eb,#1e40af);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">M Kumarasamy College of Engineering</div>
        </div>
        <button class="hamburger-idx" id="idxHamburger" onclick="toggleIdxNav()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
        <nav id="idxNav">
            <a href="#portals" class="nav-btn-secondary" onclick="closeIdxNav()">Explore</a>
            <a href="/campuss/admin/login.php" class="nav-btn-primary">Enter Portal</a>
        </nav>
    </header>
    <script>
    function toggleIdxNav() {
        document.getElementById('idxNav').classList.toggle('open');
        document.getElementById('idxHamburger').classList.toggle('open');
    }
    function closeIdxNav() {
        document.getElementById('idxNav').classList.remove('open');
        document.getElementById('idxHamburger').classList.remove('open');
    }
    </script>

    <section class="hero" id="portals">
        <div class="hero-container">
            <h1 class="college-name">M KUMARASAMY COLLEGE OF ENGINEERING</h1>
            <p class="website-title">CAMPUS CONNECT</p>

            <div class="portal-selector">
                <div class="portal-card">
                    <h2 class="portal-name">Student</h2>
                    <p class="portal-desc">Access your placement dashboard, apply for drives, track results and manage your profile</p>
                    <a href="/campuss/student/login.php" class="portal-btn">Student Login</a>
                </div>

                <div class="portal-card">
                    <h2 class="portal-name">Staff</h2>
                    <p class="portal-desc">Manage department activities, verify students, view statistics and generate reports</p>
                    <a href="/campuss/staff/login.php" class="portal-btn">Staff Login</a>
                </div>

                <div class="portal-card">
                    <h2 class="portal-name">Admin</h2>
                    <p class="portal-desc">Control all operations, manage drives, companies, results and system-wide announcements</p>
                    <a href="/campuss/admin/login.php" class="portal-btn">Admin Login</a>
                </div>
            </div>
        </div>
    </section>
    <script src="/campuss/js/theme-manager.js"></script>
</body>
</html>
