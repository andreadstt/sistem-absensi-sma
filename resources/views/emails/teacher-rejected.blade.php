<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Ditolak</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f5f5f5; padding:40px;">

<div style="max-width:600px;margin:auto;background:white;padding:30px;border-radius:10px;border:1px solid #ddd;">

    <h2 style="color:#dc2626;margin-top:0;">
        Pendaftaran Akun Guru Ditolak
    </h2>

    <p>Yth. <strong>{{ $name }}</strong>,</p>

    <p>
        Terima kasih telah melakukan pendaftaran akun guru pada
        <strong>Sistem Absensi SMAN 10 Kota Bogor</strong>.
    </p>

    <p>
        Setelah dilakukan proses verifikasi oleh administrator,
        mohon maaf pendaftaran Anda <strong>belum dapat disetujui</strong>.
    </p>

    <div style="background:#fef2f2;border-left:5px solid #dc2626;padding:15px;margin:25px 0;">
        <strong>Alasan penolakan:</strong>
        <br><br>
        {{ $reason }}
    </div>

    <p>
        Anda dapat memperbaiki data yang diperlukan kemudian
        melakukan pendaftaran kembali melalui tombol berikut.
    </p>

    <p style="text-align:center;margin:35px 0;">
        <a href="{{ $registerUrl }}"
           style="background:#2563eb;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">
            Daftar Kembali
        </a>
    </p>

    <p>
        Apabila terdapat pertanyaan, silakan menghubungi administrator sekolah.
    </p>

    <hr style="margin:30px 0;">

    <small style="color:#777;">
        Email ini dikirim secara otomatis oleh Sistem Absensi SMAN 10 Kota Bogor.
    </small>

</div>

</body>
</html>