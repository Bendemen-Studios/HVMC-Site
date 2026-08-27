<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Ongeldige aanvraag.']);
    exit;
}

// Honeypot tegen simpele spambots.
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$minecraft = trim((string)($_POST['minecraft_name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$motivation = trim((string)($_POST['motivation'] ?? ''));

if ($minecraft === '' || $motivation === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Vul alle velden correct in.']);
    exit;
}

// Beperk invoerlengtes om misbruik te voorkomen.
if (mb_strlen($minecraft) > 100 || mb_strlen($email) > 254 || mb_strlen($motivation) > 5000) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Een of meer velden zijn te lang.']);
    exit;
}

$to = 'info@bendemen.nl';
$subject = '[!| Aanmelding HVMC';
$body = "[!| Aanmelding HVMC\n\n" .
        "Minecraft Naam: {$minecraft}\n" .
        "Emailadres voor Reactie: {$email}\n\n" .
        "Speel Motivatie\n" .
        "{$motivation}\n";

$headers = [
    'From: Hero\'s Vault <noreply@hvmc.nl>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8'
];

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'De e-mail kon niet worden verzonden. Probeer het later opnieuw.']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Je aanmelding is verzonden!']);
