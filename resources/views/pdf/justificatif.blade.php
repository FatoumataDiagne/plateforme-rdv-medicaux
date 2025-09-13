<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificatif de Rendez-vous Médical</title>
    <style>
        /* Styles améliorés - version compacte */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #2d3748;
            background-color: #fff;
            line-height: 1.4;
            font-size: 14px;
        }
        
        .container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        /* Grand titre centré */
        .main-title {
            text-align: center;
            font-size: 32px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 10px 0 5px 0; /* espacement réduit */
            color: #1d4ed8;
            letter-spacing: 2px;
        }
        
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        
        .logo {
            margin-bottom: 10px;
            font-size: 36px;
        }
        
        .content {
            padding: 25px;
        }
        
        .section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        h2 {
            color: #2d3748;
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 18px;
            font-weight: 700;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .info-item {
            margin-bottom: 12px;
        }
        
        .label {
            font-weight: 700;
            color: #4a5568;
            display: block;
            margin-bottom: 3px;
            font-size: 13px;
        }
        
        .value {
            font-size: 14px;
            color: #2d3748;
        }
        
        .footer {
            background: #f7fafc;
            padding: 15px 25px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        
        .reference {
            background: #f7fafc;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #2563eb;
            margin-top: 15px;
            font-size: 13px;
        }
        
        .important-note {
            background: #fffbeb;
            border-left: 3px solid #ed8936;
            padding: 12px;
            margin: 15px 0;
            border-radius: 6px;
            font-size: 13px;
        }
        
        .important-note strong {
            font-weight: 700;
        }
        
        .important-note ul {
            margin: 8px 0;
            padding-left: 20px;
        }
        
        .important-note li {
            margin-bottom: 4px;
        }
        
        .barcode {
            text-align: center;
            margin: 15px 0;
            padding: 8px;
            background: #f7fafc;
            border-radius: 6px;
            font-family: monospace;
        }
        
        .signature-area {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e0;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 45%;
        }
        
        .signature-line {
            border-bottom: 1px solid #cbd5e0;
            height: 30px;
            margin-top: 25px;
        }
        
        @media (max-width: 640px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                margin: 10px;
                border-radius: 6px;
            }
            
            .header, .content {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .main-title {
                font-size: 26px;
            }
            
            .signature-area {
                flex-direction: column;
            }
            
            .signature-box {
                width: 100%;
                margin-bottom: 20px;
            }
        }
        
        /* Style pour l'impression */
        @media print {
            body {
                background-color: white;
                font-size: 12px;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
                border-radius: 0;
            }
            
            .header {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                padding: 15px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .footer {
                background: #f7fafc;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                padding: 12px 20px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Grand Titre -->
        <div class="main-title">Justificatif de Rendez-vous</div>

        <!-- En-tête -->
        <div class="header">
            <div class="logo">⚕️</div>
            <h1>Document officiel de confirmation</h1>
        </div>

        <div class="content">
            <div class="section">
                <h2>Informations Patient</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Nom complet</span>
                        <span class="value">{{ $rendezVous->patient->prenom }} {{ $rendezVous->patient->nom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Email</span>
                        <span class="value">{{ $rendezVous->patient->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Téléphone</span>
                        <span class="value">{{ $rendezVous->patient->telephone ?? 'Non renseigné' }}</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Détails du Rendez-vous</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Médecin</span>
                        <span class="value">Dr. {{ $rendezVous->medecin->user->prenom }} {{ $rendezVous->medecin->user->nom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Spécialité</span>
                        <span class="value">{{ $rendezVous->medecin->specialite->nom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Date</span>
                        <span class="value">{{ \Carbon\Carbon::parse($rendezVous->date)->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Heure</span>
                        <span class="value">{{ $rendezVous->heure_debut }} - {{ $rendezVous->heure_fin }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Lieu</span>
                        <span class="value">{{ $rendezVous->medecin->adresse_cabinet }}</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Informations de Paiement</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Mode de paiement</span>
                        <span class="value">{{ ucfirst($rendezVous->mode_paiement) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Statut</span>
                        <span class="value">{{ ucfirst($rendezVous->statut_paiement) }}</span>
                    </div>
                    @if($rendezVous->montant)
                    <div class="info-item">
                        <span class="label">Montant</span>
                        <span class="value">{{ number_format($rendezVous->montant, 2, ',', ' ') }} €</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="important-note">
                <strong>📋 Instructions importantes :</strong>
                <ul>
                    <li>Présentez ce justificatif à l'accueil</li>
                    <li>Arrivez 10 minutes avant l'heure du rendez-vous</li>
                    <li>Apportez votre carte vitale et pièce d'identité</li>
                </ul>
            </div>
            
            <div class="barcode">
                <div style="font-family: monospace; letter-spacing: 3px; font-size: 16px;">
                    {{ $reference }}
                </div>
                <p>Référence: {{ $reference }}</p>
            </div>
            
            <div class="reference">
                <div class="info-item">
                    <span class="label">Document généré le</span>
                    <span class="value">{{ $dateGeneration }}</span>
                </div>
            </div>
            
            <div class="signature-area">
                <div class="signature-box">
                    <span class="label">Signature du patient</span>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-box">
                    <span class="label">Cachet et signature du médecin</span>
                    <div class="signature-line"></div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Pour toute question, contactez-nous au +33 1 23 45 67 89 ou contact@medicapp.fr</p>
        </div>
    </div>
    
    <!-- Bouton d'impression pour PDF -->
    <div class="no-print" style="position: fixed; bottom: 15px; right: 15px;">
        <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-size: 13px;">
            📄 Télécharger le PDF
        </button>
    </div>
</body>
</html>
