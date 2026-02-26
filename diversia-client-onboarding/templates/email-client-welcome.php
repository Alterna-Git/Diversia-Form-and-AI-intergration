<?php if (!defined('ABSPATH')) exit; ?>
<?php
// Variables available: $first_name, $company_name, $login_url, $email, $dashboard_url
$site_url  = home_url();
$site_name = get_bloginfo('name');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>¡Bienvenido(a) a Diversia Health!</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background:#f4f7f9;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7f9;padding:30px 0;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
          <td style="background:#1B3A5C;padding:32px 40px;text-align:center;">
            <h1 style="color:#ffffff;margin:0;font-size:26px;letter-spacing:-0.5px;">Diversia Health</h1>
            <p style="color:#A8C8D8;margin:6px 0 0;font-size:13px;letter-spacing:1px;text-transform:uppercase;">
              Clinical Trial Recruitment Platform
            </p>
          </td>
        </tr>

        <!-- Spanish Section -->
        <tr>
          <td style="padding:40px 40px 24px;">
            <div style="border-left:4px solid #2E86AB;padding-left:16px;margin-bottom:24px;">
              <span style="color:#2E86AB;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">ESPAÑOL</span>
            </div>
            <h2 style="color:#1B3A5C;margin:0 0 16px;font-size:22px;">
              ¡Bienvenido(a) a Diversia Health, <?php echo $first_name; ?>!
            </h2>
            <p style="color:#444;line-height:1.7;margin:0 0 16px;">
              Es un placer darle la bienvenida a la plataforma de reclutamiento de ensayos clínicos de Diversia Health.
              Su organización, <strong><?php echo $company_name; ?></strong>, ha completado exitosamente el proceso de incorporación
              y su cuenta de cliente ya está activa.
            </p>
            <p style="color:#444;line-height:1.7;margin:0 0 24px;">
              A partir de ahora, podrá acceder a nuestro panel de control para gestionar sus ensayos clínicos,
              revisar participantes calificados y monitorear el progreso de su reclutamiento.
            </p>

            <div style="background:#f0f7fb;border-radius:6px;padding:20px;margin-bottom:24px;">
              <p style="margin:0 0 8px;color:#666;font-size:13px;font-weight:bold;text-transform:uppercase;">Sus credenciales de acceso</p>
              <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="padding:4px 0;color:#444;width:120px;">Correo:</td>
                  <td style="padding:4px 0;color:#1B3A5C;font-weight:bold;"><?php echo $email; ?></td>
                </tr>
                <tr>
                  <td style="padding:4px 0;color:#444;">Acceso:</td>
                  <td style="padding:4px 0;"><a href="<?php echo $login_url; ?>" style="color:#2E86AB;"><?php echo $login_url; ?></a></td>
                </tr>
              </table>
            </div>

            <div style="text-align:center;margin-bottom:8px;">
              <a href="<?php echo $login_url; ?>" style="display:inline-block;background:#2E86AB;color:#ffffff;padding:14px 32px;border-radius:6px;text-decoration:none;font-size:15px;font-weight:bold;letter-spacing:0.3px;">
                Acceder al Panel de Control →
              </a>
            </div>
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style="padding:0 40px;">
            <hr style="border:none;border-top:2px dashed #e0e8ef;margin:0;">
          </td>
        </tr>

        <!-- English Section -->
        <tr>
          <td style="padding:24px 40px 40px;">
            <div style="border-left:4px solid #1B3A5C;padding-left:16px;margin-bottom:24px;">
              <span style="color:#1B3A5C;font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">ENGLISH</span>
            </div>
            <h2 style="color:#1B3A5C;margin:0 0 16px;font-size:22px;">
              Welcome to Diversia Health, <?php echo $first_name; ?>!
            </h2>
            <p style="color:#444;line-height:1.7;margin:0 0 16px;">
              We are delighted to welcome <strong><?php echo $company_name; ?></strong> to the Diversia Health
              clinical trial recruitment platform. Your client account is now fully active and you can begin
              managing your clinical trials immediately.
            </p>
            <p style="color:#444;line-height:1.7;margin:0 0 24px;">
              From your dashboard, you can manage trials, review qualified participants, and track your
              recruitment funnel in real time.
            </p>

            <div style="background:#f0f7fb;border-radius:6px;padding:20px;margin-bottom:24px;">
              <p style="margin:0 0 8px;color:#666;font-size:13px;font-weight:bold;text-transform:uppercase;">Your Login Credentials</p>
              <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="padding:4px 0;color:#444;width:120px;">Email:</td>
                  <td style="padding:4px 0;color:#1B3A5C;font-weight:bold;"><?php echo $email; ?></td>
                </tr>
                <tr>
                  <td style="padding:4px 0;color:#444;">Login URL:</td>
                  <td style="padding:4px 0;"><a href="<?php echo $login_url; ?>" style="color:#2E86AB;"><?php echo $login_url; ?></a></td>
                </tr>
              </table>
              <p style="margin:12px 0 0;color:#888;font-size:12px;">
                For security, please change your password after your first login.
              </p>
            </div>

            <div style="text-align:center;margin-bottom:8px;">
              <a href="<?php echo $login_url; ?>" style="display:inline-block;background:#1B3A5C;color:#ffffff;padding:14px 32px;border-radius:6px;text-decoration:none;font-size:15px;font-weight:bold;letter-spacing:0.3px;">
                Access Your Dashboard →
              </a>
            </div>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f4f7f9;padding:20px 40px;text-align:center;border-top:1px solid #e0e8ef;">
            <p style="color:#999;font-size:12px;margin:0;">
              © <?php echo date('Y'); ?> Diversia Health. All rights reserved.<br>
              <a href="<?php echo esc_url($site_url); ?>" style="color:#2E86AB;text-decoration:none;"><?php echo $site_name; ?></a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
