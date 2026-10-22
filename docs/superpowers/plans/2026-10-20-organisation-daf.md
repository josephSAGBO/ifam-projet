# Plan d'implémentation pour l'Organisation du Département Administratif et Financier (DAF)

**Objectif :** Organiser les accès, responsabilités, cloisonnements multi-sites et sidebars du DAF (Service Administratif, Service Financier, RAF, Direction Générale DAF) en réutilisant les modules existants sans recréation et en assurant la limitation stricte sur les résultats pédagogiques.

**Architecture :** Attribution des rôles et permissions Spatie aux profils DAF, extension non-destructive de la table `payments` pour la chaîne de validation financière (enregistrement vs validation), filtrage par site dans les contrôleurs administratifs et financiers (PaymentController, FeeController, ExpenseController, EnrollmentController), verrouillage serveur strict contre toute modification de notes/évaluations par le DAF, et sidebar administrative adaptative.

**Pile technique :** Laravel 12, PHP 8.4, Spatie Laravel-Permission, Blade, SQLite / MySQL.

**Spécification :** PROMPT 2 — IFAM CONNECT : ORGANISATION DU DÉPARTEMENT ADMINISTRATIF ET FINANCIER

## Contraintes globales

- Conserver impérativement les maquettes et l'identité visuelle validées (Google Stitch / tokens IFAM).
- Ne pas recréer de second module d'inscription, de finance ou de documents.
- Ne pas modifier les notes ou résultats pédagogiques depuis les rôles DAF.
- Traçabilité et historique complet des opérations financières (auteur, approbateur, statut de validation).
- Cloisonnement multi-sites strict : vue transversale uniquement pour la DG DAF (et SuperAdmin/Direction), vue locale par site pour RAF et agents.

## Focus de la revue

- Tentative de modification de note par un comptable ou agent DAF -> HTTP 403 Forbidden.
- Tentative de consultation/création d'un paiement hors-site par un caissier -> HTTP 403 Forbidden.
- Validation d'un paiement par un caissier/comptable -> refusé (réservé à Direction Générale DAF / Administrateur).
- Paiement enregistré par un caissier -> état 'en_attente', reçu éditable avec mention du statut.
- Paiement validé par la Direction Générale DAF -> état 'valide', traçabilité `validated_by` et `validated_at`.
- Responsable administratif et financier -> supervision des 2 services de son site, pas de droits transversaux sur les autres sites.
- Direction Générale DAF -> vue transversale sur les paiements, frais, dépenses et inscriptions de tous les sites.
- Les paiements historiques et existants continuent de fonctionner sans régression.

---

### Tâche 1 : Migration & Modèle Payment pour la validation financière

**Fichiers :**
- Créer : `database/migrations/2026_10_20_160500_add_validation_columns_to_payments_table.php`
- Modifier : `app/Models/Payment.php`
- Modifier : `database/seeders/RoleSeeder.php`

- [ ] Étape 1 : Créer la migration réversible ajoutant `status` ('en_attente', 'valide', 'rejete'), `validated_by`, `validated_at`, `validation_notes` à la table `payments`, avec mise à jour des paiements existants à 'valide' pour préserver l'existant.
- [ ] Étape 2 : Mettre à jour `Payment.php` pour ajouter ces colonnes dans `$fillable`, les casts et la relation `validateur(): BelongsTo(User::class, 'validated_by')`.
- [ ] Étape 3 : Mettre à jour `RoleSeeder.php` avec les rôles institutionnels DAF et leurs permissions Spatie (`validate financial operations`, `create payments`, `view fees`, `view payments`, `print receipts`, `manage enrollments`, etc.).

---

### Tâche 2 : Contrôle serveur & Sécurité dans les contrôleurs Financiers et Administratifs

**Fichiers :**
- Modifier : `app/Http/Controllers/Admin/PaymentController.php`
- Modifier : `app/Http/Controllers/Admin/FeeController.php`
- Modifier : `app/Http/Controllers/Admin/ExpenseController.php`
- Modifier : `app/Http/Controllers/Admin/EnrollmentController.php`
- Modifier : `app/Http/Controllers/Admin/GradeController.php`
- Modifier : `app/Http/Controllers/Admin/EvaluationController.php`
- Modifier : `app/Http/Controllers/Admin/BulletinController.php`

- [ ] Étape 1 : Dans `PaymentController`, implémenter le filtrage par site pour les agents locaux et RAF, l'action `validatePayment()` et `rejectPayment()` réservée à `validate financial operations`, et adapter l'enregistrement (`status = 'en_attente'`).
- [ ] Étape 2 : Dans `FeeController` et `ExpenseController`, restreindre la visibilité aux sites autorisés pour les rôles locaux.
- [ ] Étape 3 : Dans `EnrollmentController`, restreindre la visibilité et la création aux sites autorisés pour les rôles locaux.
- [ ] Étape 4 : Dans `GradeController`, `EvaluationController` et `BulletinController`, bloquer formellement toute modification/publication (HTTP 403) pour tout utilisateur agissant sous un rôle DAF.

---

### Tâche 3 : Routes & Organisation des Sidebars DAF

**Fichiers :**
- Modifier : `routes/web.php`
- Modifier : `resources/views/layouts/admin.blade.php`
- Modifier : `resources/views/admin/payments/index.blade.php`
- Modifier : `resources/views/admin/payments/receipt.blade.php`

- [ ] Étape 1 : Dans `routes/web.php`, ouvrir le groupe `admin` aux rôles DAF et déclarer les routes `payments.validate` et `payments.reject`.
- [ ] Étape 2 : Dans `layouts/admin.blade.php`, organiser la sidebar selon les 4 profils DAF (Direction Générale DAF, RAF, Service Administratif, Service Financier).
- [ ] Étape 3 : Dans `payments/index.blade.php` et `payments/receipt.blade.php`, afficher les badges de statut de validation et le bouton d'approbation pour les utilisateurs autorisés.

---

### Tâche 4 : Tests Fonctionnels & Non-Régression

**Fichiers :**
- Créer : `tests/Feature/Admin/DafOrganizationAndSecurityTest.php`

- [ ] Étape 1 : Écrire les 8 tests obligatoires de la spécification (Section 12).
- [ ] Étape 2 : Exécuter et valider que tous les tests passent à 100%.
- [ ] Étape 3 : Exécuter `vendor/bin/pint --dirty --format agent`.
