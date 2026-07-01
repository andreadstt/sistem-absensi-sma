<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Guru Disetujui</title>
</head>

<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px;">
        <div
            style="background:#ffffff;border-radius:16px;padding:32px;border:1px solid #e5e7eb;box-shadow:0 6px 20px rgba(15,23,42,0.06);">
            <h1 style="margin:0 0 16px;font-size:24px;line-height:1.3;color:#111827;">Akun Guru Anda Telah Disetujui</h1>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.7;">Halo {{ $name }}, akun guru Anda sudah
                aktif. Berikut detail login yang dapat digunakan:</p>

            <table style="width:100%;border-collapse:collapse;margin:0 0 24px;">
                <tr>
                    <td style="padding:10px 0;width:180px;color:#6b7280;">Nama</td>
                    <td style="padding:10px 0;font-weight:600;">{{ $name }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 0;width:180px;color:#6b7280;">Email</td>
                    <td style="padding:10px 0;font-weight:600;">{{ $email }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 0;width:180px;color:#6b7280;">Default Password</td>
                    <td style="padding:10px 0;font-weight:600;">{{ $defaultPassword }}</td>
                </tr>
            </table>

            <div style="text-align:center;margin:28px 0;">
                <a href="{{ $loginUrl }}"
                    style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:10px;font-weight:700;">Login
                    Sekarang</a>
            </div>

            <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;">Gunakan password di atas untuk login
                pertama kali. Jika sudah masuk, disarankan segera mengganti password.</p>
        </div>
    </div>
</body>

</html>
