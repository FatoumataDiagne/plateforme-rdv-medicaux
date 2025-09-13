<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmation de Rendez-vous</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
        .details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        .button { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Confirmation de Rendez-vous</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $rendezVous->patient->prenom }} {{ $rendezVous->patient->nom }}</strong>,</p>
            <p>Votre rendez-vous médical a été confirmé avec succès.</p>
            
            <div class="details">
                <h3>📋 Détails du Rendez-vous</h3>
                <p><strong>Médecin :</strong> Dr. {{ $rendezVous->medecin->user->prenom }} {{ $rendezVous->medecin->user->nom }}</p>
                <p><strong>Spécialité :</strong> {{ $rendezVous->specialite->nom ?? 'Non spécifiée' }}</p>
                <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($rendezVous->date)->format('d/m/Y') }}</p>
                <p><strong>Heure :</strong> {{ substr($rendezVous->heure_debut, 0, 5) }} - {{ substr($rendezVous->heure_fin, 0, 5) }}</p>
                <p><strong>Référence :</strong> RDV-{{ str_pad($rendezVous->id, 6, '0', STR_PAD_LEFT) }}</p>
                
                @if($rendezVous->montant > 0)
                <p><strong>Montant :</strong> {{ number_format($rendezVous->montant, 2, ',', ' ') }} €</p>
                <p><strong>Statut Paiement :</strong> {{ ucfirst($rendezVous->statut_paiement) }}</p>
                @endif
            </div>

            <p>📍 <strong>Lieu :</strong> {{ $rendezVous->medecin->adresse_cabinet }}, {{ $rendezVous->medecin->code_postal }} {{ $rendezVous->medecin->ville }}</p>
            
            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/rendez-vous') }}" class="button">Voir Mes Rendez-vous</a>
            </div>

            <p>ℹ️ <em>Pensez à annuler 24h à l'avance en cas d'empêchement.</em></p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Plateforme Médicale. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>