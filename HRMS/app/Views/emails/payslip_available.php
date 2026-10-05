<?php
/** @var string $period */
?>
<!doctype html>
<html>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:24px; margin:0;">
  <table role="presentation" width="100%" style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:6px; overflow:hidden;">
    <tr>
      <td style="padding:24px;">
        <h2 style="margin:0 0 16px; font-size:18px; color:#1b1f1d;">Your payslip is ready</h2>
        <p style="margin:0; font-size:14px; color:#3a3f3d;">
          Your payslip for <strong><?= esc($period) ?></strong> is now available. Sign in to My Payroll to view or download it.
        </p>
      </td>
    </tr>
  </table>
</body>
</html>
