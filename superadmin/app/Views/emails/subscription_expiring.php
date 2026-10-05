<?php
/** @var string $companyName */
/** @var string $planName */
/** @var string $expiresAt */
/** @var int $daysLeft */
?>
<!doctype html>
<html>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px; margin:0;">
  <table role="presentation" width="100%" style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:6px; overflow:hidden;">
    <tr>
      <td style="padding:24px;">
        <h2 style="margin:0 0 16px; font-size:18px; color:#1b1f1d;">Your subscription is expiring soon</h2>
        <p style="margin:0 0 16px; font-size:14px; color:#3a3f3d;">
          The <strong><?= esc($planName) ?></strong> subscription for <strong><?= esc($companyName) ?></strong>
          expires on <strong><?= esc($expiresAt) ?></strong> (<?= (int) $daysLeft ?> day<?= $daysLeft === 1 ? '' : 's' ?> from now).
        </p>
        <p style="margin:0; font-size:14px; color:#3a3f3d;">
          Please contact your account manager to renew before this date to avoid any interruption of access.
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
