<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Annulation de Rendez-vous</title>
    <style>/* Styles similaires aux autres templates */</style>
</head>
<body>
    <div class="container">
        <div class="header" style="background: #e74c3c;">
            <h1>❌ Annulation de Rendez-vous</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $rendezVous->patient->user->name }}</strong>,</p>
            <p>Votre rendez-vous médical a été annulé.</p>
            
            @if($raison)
            <p><strong>Raison :</strong> {{ $raison }}</p>
            @endif
            
            <div class="details">
                <h3>📋 Rendez-vous annulé</h3>
                <p><strong>Médecin :</strong> Dr. {{ $rendezVous->medecin->user->name }}</p>
                <p><strong>Date :</strong> {{ $rendezVous->date->format('d/m/Y') }}</p>
                <p><strong>Heure :</strong> {{ substr($rendezVous->heure_debut, 0, 5) }}</p>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/rendez-vous') }}" class="button" style="background: #e74c3c;">
                    Prendre un nouveau rendez-vous
                </a>
            </div>

            <p>Nous sommes désolés pour ce contretemps.</p>
        </div>
    </div>
</body>
</html>