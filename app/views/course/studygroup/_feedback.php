<?
# Lifter010: TODO
if ($errors = $flash['errors']) {
    if ($flash['create']) {
        echo MessageBox::error(_("Beim Anlegen der Studiengruppe traten folgende Fehler auf:"), $errors);
    } elseif ($flash['edit']) {
        echo MessageBox::error(_("Beim Bearbeiten der Studiengruppe traten folgende Fehler auf:"), $errors);
    }
}

if ($messages = $flash['messages']) {
    foreach ($messages as $type => $message_data) {
        echo MessageBox::$type( $message_data['title'], $message_data['details']);
    }
}
