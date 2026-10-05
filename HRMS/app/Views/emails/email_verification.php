<?php
/** @var string $name */
/** @var string $verificationLink */
/** @var int $expiresHours */
?>
<!doctype html>
<html>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px; margin:0;">
  <table role="presentation" width="100%" style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:6px; overflow:hidden;">
    <tr>
      <td style="padding:24px;">
        <h2 style="margin:0 0 16px; font-size:18px; color:#1b1f1d;">Verify your email address</h2>
        <p style="margin:0 0 16px; font-size:14px; color:#3a3f3d;">Hi <?= esc($name) ?>,</p>
        <p style="margin:0 0 16px; font-size:14px; color:#3a3f3d;">
          Please confirm this is your email address to finish setting up your account.
          This link expires in <?= (int) $expiresHours ?> hours.
        </p>
        <p style="margin:0 0 24px;">
          <a href="<?= esc($verificationLink) ?>" style="display:inline-block; padding:10px 20px; background:<?= esc(company_accent_rgba()) ?>; color:#ffffff; text-decoration:none; border-radius:4px; font-size:14px;">Verify email</a>
        </p>
        <p style="margin:0; font-size:12px; color:#6b7280;">
          If the button doesn't work, copy and paste this link: <?= esc($verificationLink) ?>
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
