<?php
/**
 * =====================================================
 * CAMPUS RECRUITMENT - EMAIL MAILER (SMTP via Gmail)
 * =====================================================
 * NO Composer needed — loads PHPMailer manually.
 *
 * SETUP:
 * 1. Download: https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip
 * 2. From the ZIP, copy the 3 files inside src/ to:
 *       C:\xampp\htdocs\campuss\vendor\phpmailer\phpmailer\src\
 *    The 3 files are: PHPMailer.php, SMTP.php, Exception.php
 * 3. Fill in your Gmail and App Password below.
 *
 * HOW TO GET GMAIL APP PASSWORD:
 *   → myaccount.google.com → Security
 *   → Enable 2-Step Verification
 *   → Search "App passwords" → Create one for "Mail"
 *   → Copy the 16-character password and paste below
 * =====================================================
 */

// ── CONFIGURE THESE ──────────────────────────────────
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USERNAME', 'varshinichellamuthu1708@gmail.com');      // ← Change this
define('SMTP_PASSWORD', 'eghs wekb cyob pbug');       // ← Change this (App Password, 16 chars)
define('SMTP_FROM_NAME','Placement Cell');
define('COLLEGE_NAME',  'M Kumarasamy College of Engineering,Karur');          // ← Change this
// ─────────────────────────────────────────────────────

// Load PHPMailer manually (no Composer)
$_phpmailer_src = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';

if (!file_exists($_phpmailer_src . 'PHPMailer.php')) {
    error_log('[Mailer] PHPMailer not found at ' . $_phpmailer_src);
    error_log('[Mailer] Download from https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip');
    error_log('[Mailer] Copy src/PHPMailer.php, src/SMTP.php, src/Exception.php to ' . $_phpmailer_src);
    // Define dummy functions so site still works even without email
    function sendResultEmail($to, $name, $subject, $html) { return false; }
    function sendSelectionEmail($to, $name, $company, $pkg, $role='') { return false; }
    function sendRoundResultEmail($to, $name, $company, $round, $status) { return false; }
    function sendRejectionEmail($to, $name, $company) { return false; }
    function sendApprovalEmail($to, $name) { return false; }
    return;
}

require_once $_phpmailer_src . 'Exception.php';
require_once $_phpmailer_src . 'PHPMailer.php';
require_once $_phpmailer_src . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Core send function — handles all SMTP sending
 */
function sendResultEmail($toEmail, $toName, $subject, $htmlBody) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(SMTP_USERNAME, SMTP_FROM_NAME);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        error_log("[Mailer] ✅ Email sent to $toEmail | $subject");
        return true;
    } catch (Exception $e) {
        error_log("[Mailer] ❌ Failed to $toEmail: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Selected by company — Congratulations email
 */
function sendSelectionEmail($toEmail, $toName, $companyName, $package, $role = '') {
    $roleText = $role ? " for the role of <strong>$role</strong>" : '';
    $subject  = "🎉 Congratulations! You've been Selected by $companyName";
    $html = emailTemplate("Congratulations, $toName! 🎉", "
        <p>We are thrilled to inform you that you have been
           <strong style='color:#16a34a;'>SELECTED</strong>
           by <strong>$companyName</strong>$roleText.</p>
        <div style='background:#f0fdf4;border:2px solid #86efac;border-radius:10px;
                    padding:24px;margin:24px 0;text-align:center;'>
            <div style='font-size:42px;'>🏆</div>
            <div style='font-size:22px;font-weight:bold;color:#15803d;margin:10px 0;'>
                You are Selected!</div>
            <div style='color:#166534;font-size:16px;'>
                Package Offered: <strong>$package</strong></div>
        </div>
        <p>Please log in to your
           <a href='http://localhost/campuss/student/results.php'
              style='color:#2563eb;'>student portal</a>
           for further details and next steps.</p>
        <p>Your hard work has paid off — congratulations! 🌟</p>
        <p>Best wishes,<br><strong>" . SMTP_FROM_NAME . "</strong><br>" . COLLEGE_NAME . "</p>
    ");
    return sendResultEmail($toEmail, $toName, $subject, $html);
}

/**
 * Round 1 or Round 2 result email
 */
function sendRoundResultEmail($toEmail, $toName, $companyName, $round, $status) {
    $passed = ($status === 'pass');
    $icon   = $passed ? '✅' : '❌';
    $color  = $passed ? '#16a34a'  : '#dc2626';
    $bg     = $passed ? '#f0fdf4'  : '#fef2f2';
    $border = $passed ? '#86efac'  : '#fca5a5';
    $result = $passed ? 'PASSED'   : 'NOT CLEARED';
    $next   = $passed
        ? 'Congratulations! Prepare well for the next round. Stay focused and confident!'
        : "Don't be discouraged — keep working hard and apply to upcoming drives. You've got this!";
    $subject = "$icon $round Result — $companyName | $result";
    $html = emailTemplate("$round Result — $companyName", "
        <p>Dear <strong>$toName</strong>,</p>
        <p>Here is your <strong>$round</strong> result for the placement drive at
           <strong>$companyName</strong>:</p>
        <div style='background:$bg;border:2px solid $border;border-radius:10px;
                    padding:24px;margin:24px 0;text-align:center;'>
            <div style='font-size:42px;'>$icon</div>
            <div style='font-size:20px;font-weight:bold;color:$color;margin:10px 0;'>
                $result</div>
            <div style='color:#374151;'>$round — $companyName</div>
        </div>
        <p>$next</p>
        <p>Check your <a href='http://localhost/campuss/student/results.php'
           style='color:#2563eb;'>results page</a> for full details.</p>
        <p>Best regards,<br><strong>" . SMTP_FROM_NAME . "</strong><br>" . COLLEGE_NAME . "</p>
    ");
    return sendResultEmail($toEmail, $toName, $subject, $html);
}

/**
 * Final rejection email
 */
function sendRejectionEmail($toEmail, $toName, $companyName) {
    $subject = "Drive Result Update — $companyName";
    $html = emailTemplate("Drive Result — $companyName", "
        <p>Dear <strong>$toName</strong>,</p>
        <p>We regret to inform you that your application for
           <strong>$companyName</strong> was not successful in this drive.</p>
        <div style='background:#fffbeb;border:2px solid #fcd34d;border-radius:10px;
                    padding:24px;margin:24px 0;text-align:center;'>
            <div style='font-size:42px;'>💪</div>
            <div style='font-size:18px;font-weight:bold;color:#92400e;margin:10px 0;'>
                Keep Going!</div>
            <div style='color:#78350f;'>More opportunities are coming your way.</div>
        </div>
        <p>Don't be disheartened — every experience is a learning opportunity.
           Keep sharpening your skills and watch out for upcoming placement drives.</p>
        <p>Visit your <a href='http://localhost/campuss/student/drives.php'
           style='color:#2563eb;'>student portal</a> to apply for new drives.</p>
        <p>Best regards,<br><strong>" . SMTP_FROM_NAME . "</strong><br>" . COLLEGE_NAME . "</p>
    ");
    return sendResultEmail($toEmail, $toName, $subject, $html);
}

/**
 * Account approved email
 */
function sendApprovalEmail($toEmail, $toName) {
    $subject = "✅ Your Account is Approved — Campus Recruitment Portal";
    $html = emailTemplate("Account Approved!", "
        <p>Dear <strong>$toName</strong>,</p>
        <p>Great news! Your account on the <strong>" . COLLEGE_NAME . " Campus Recruitment Portal</strong>
           has been <strong style='color:#16a34a;'>verified and approved</strong>.</p>
        <div style='background:#f0fdf4;border:2px solid #86efac;border-radius:10px;
                    padding:24px;margin:24px 0;text-align:center;'>
            <div style='font-size:42px;'>✅</div>
            <div style='font-size:18px;font-weight:bold;color:#15803d;margin:10px 0;'>
                You can now apply for placement drives!</div>
        </div>
        <p style='text-align:center;margin-top:24px;'>
            <a href='http://localhost/campuss/student/login.php'
               style='background:#2563eb;color:#fff;padding:12px 30px;border-radius:8px;
                      text-decoration:none;font-weight:bold;font-size:15px;'>
               Login to Portal →
            </a>
        </p>
        <p>Best of luck in your placement journey!<br>
           <strong>" . SMTP_FROM_NAME . "</strong></p>
    ");
    return sendResultEmail($toEmail, $toName, $subject, $html);
}

/**
 * HTML email template wrapper
 */
function emailTemplate($heading, $bodyContent) {
    $college = COLLEGE_NAME;
    return "<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f3f4f6;padding:30px 0;'>
<tr><td align='center'>
<table width='600' cellpadding='0' cellspacing='0'
       style='background:#fff;border-radius:12px;overflow:hidden;
              box-shadow:0 4px 16px rgba(0,0,0,0.08);max-width:600px;'>
  <tr>
    <td style='background:linear-gradient(135deg,#1e40af,#3b82f6);
               padding:30px 40px;text-align:center;'>
      <div style='font-size:12px;color:#bfdbfe;text-transform:uppercase;
                  letter-spacing:2px;'>$college</div>
      <div style='font-size:24px;font-weight:bold;color:#fff;margin-top:6px;'>
          Campus Recruitment Cell</div>
    </td>
  </tr>
  <tr>
    <td style='padding:36px 40px;color:#1f2937;font-size:15px;line-height:1.7;'>
      <h2 style='color:#1e3a8a;margin:0 0 20px;font-size:22px;'>$heading</h2>
      $bodyContent
    </td>
  </tr>
  <tr>
    <td style='background:#f8fafc;padding:20px 40px;
               border-top:1px solid #e2e8f0;text-align:center;'>
      <p style='color:#94a3b8;font-size:12px;margin:0;'>
        This is an automated email from <strong>$college</strong>
        Campus Recruitment Portal.<br>Please do not reply to this email.
      </p>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>";
}
