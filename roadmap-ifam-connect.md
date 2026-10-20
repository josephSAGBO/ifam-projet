# Roadmap — IFAM Connect

> Basé sur le Cahier des charges IFAM Connect v1.0 (Septembre 2026)
> Maquettes : projet Google Stitch **"IFAM Connect Design System"** (connecté à Antigravity via MCP)

## Contexte du projet
Plateforme numérique de gestion et de suivi de l'établissement IFAM, centralisant les données administratives, pédagogiques et financières.

- **Stack technique** : Laravel + MySQL, Livewire (web uniquement pour l'instant, mobile prévu plus tard)
- **Environnement local** : Laragon (PHP, MySQL, migrations déjà fonctionnels)
- **Outil de développement assisté** : Google Antigravity
- **Maquettes** : projet Stitch "IFAM Connect Design System", connecté à Antigravity via MCP
- **Charte graphique IFAM** (ifamofficiel.com) :
  - Fond blanc, texte noir
  - Accent rose/magenta vif (boutons et éléments de marque)
  - Détail jaune/orange (trait de pinceau) réservé à la marque

## Acteurs du système (6)
| Acteur | Rôle |
|---|---|
| Administrateur | Gestion globale du système et des données |
| Direction | Consultation des informations globales |
| Enseignant | Suivi pédagogique de ses modules/étudiants |
| Étudiant | Consultation de ses informations et de son parcours |
| Parent | Suivi des informations des enfants qui lui sont liés |
| Responsable de session | **Pas un rôle distinct** — un étudiant avec une fonction supplémentaire attribuée pour une session donnée (gestion des présences de sa session) |

## Priorités de développement
1. Espace **Étudiant** en premier (création utilisateurs côté admin en tout premier)
2. Puis les autres espaces (Admin, Direction, Enseignant, Parent)
3. Web d'abord, mobile ensuite (hors périmètre v1)

---

## Page de contact (ajout au cahier des charges initial)
- Un espace de contact doit permettre à **tout utilisateur connecté** (étudiant, parent, enseignant, direction) d'envoyer un message à l'**administrateur**
- L'administrateur doit pouvoir **consulter et gérer** ces messages depuis son espace (liste des messages reçus, statut lu/non lu)
- À intégrer dans chaque espace utilisateur (accessible depuis le menu), pas seulement dans l'espace admin
- Peut s'appuyer sur le système de notifications internes déjà prévu (section 24 du cahier des charges) pour prévenir l'admin d'un nouveau message

---

## Règles de calcul et de gestion (issues du cahier des charges + annexe)

### Étudiants
- **Matricule** : généré automatiquement, unique
- **Âge** : calculé automatiquement à partir de la date de naissance (pas de saisie manuelle)

### Modules — progression
- `Progression (%) = Durée réalisée / Durée prévue × 100`
- Ex. : 30h réalisées / 40h prévues = 75 %

### Évaluations et notes
- Notes sur **20**, valeurs invalides bloquées (0 ≤ note ≤ 20)
- Chaque évaluation a un **coefficient propre** :
  - Par défaut : Interrogation = coefficient 1, Devoir = coefficient 2
  - Modifiable par l'administrateur à la création de l'évaluation
- **Moyenne de module** :
  `= Σ (note × coefficient évaluation) / Σ (coefficients évaluation)`
- **Moyenne générale de l'étudiant** :
  `= Moyenne des moyennes de module, pondérée par le coefficient de chaque module`
- Les moyennes sont **toujours calculées automatiquement**, jamais saisies manuellement

### Validation d'année
- Seuil : **moyenne générale ≥ 10/20**
- Pas de note éliminatoire en v1 (note plancher par module envisageable plus tard)
- Compensation entre modules autorisée par défaut (via la moyenne générale pondérée), aucune compensation partielle en v1
- **Seuil de présence** : proposition 75 % par module (impact bloquant ou simple avertissement → à trancher par l'administration)
- Statut **Validé / Redoublant** : calculé automatiquement en fin de session (moyenne + éventuellement présence), puis **soumis à confirmation de l'administrateur** avant d'être définitif

### Modification des notes (workflow de contrôle)
- Un enseignant ne peut pas modifier directement une note déjà enregistrée
- Une **demande de modification** doit être créée avec :
  - Ancienne valeur / Nouvelle valeur proposée / Motif / Auteur / Statut (En attente, Acceptée, Refusée) / Traité par / Date de traitement
- Seul l'**administrateur** traite les demandes (Direction = consultation seule)

### Présences
- Statuts : Présent, Absent, Absent justifié, Retard
- Le **responsable de session** saisit les présences de sa session (mais pas la sienne propre — saisie par l'enseignant)
- L'**enseignant** confirme ensuite et peut ajouter une observation
- Après confirmation, toute modification suit le **même circuit de demande** que les notes (ancienne valeur, motif, validation admin)

### Rattachement étudiant ↔ cours (règle automatique)
- Un module dépend d'une formation + année de formation
- Un cours dépend d'un module + une session
- → Étudiants concernés par un cours = étudiants ayant une **inscription active** dans la session du cours, pour la formation et l'année de formation du module concerné
- Cette règle génère automatiquement la feuille de présence et la diffusion des documents pédagogiques

### Conflits de planification (contrôle à 3 dimensions)
- Conflit de **salle** (2 cours, même salle, même horaire)
- Conflit d'**enseignant** (même enseignant, 2 cours simultanés)
- Conflit de **groupe** (mêmes étudiants convoqués à 2 cours simultanés)

### Gestion financière
- **Prix** défini **par année de formation** (pas globalement pour toute la formation)
- Réduction/bourse exprimée en **montant fixe ou pourcentage**, au choix à la saisie
- Calcul :
  - `Montant dû = Prix de la formation − Total des réductions`
  - `Solde restant = Montant dû − Total payé`
- Échéancier indicatif possible (montant + date par échéance) → signale les retards, mais paiement hors échéancier toujours possible
- Un paiement erroné n'est **jamais supprimé** : il est annulé (statut « Annulé » + motif obligatoire), un nouveau paiement correct est créé ; historique conservé
- Devise : Franc CFA (XOF) — à confirmer
- Un **reçu** (numéro, date, montant, mode) est généré à chaque paiement
- Inscription abandonnée : le solde dû reste affiché tel quel, pas de remboursement/annulation automatique

### Comptes et authentification
- Identifiant de connexion : **matricule** pour l'étudiant, **email** pour parent/enseignant/direction/admin
- Mot de passe : 8 caractères minimum, changement obligatoire à la 1ère connexion et à chaque réinitialisation
- Réinitialisation en v1 : uniquement par l'administrateur (pas de libre-service)
- Expiration de session : proposition 30 minutes d'inactivité

> **Point à trancher avant développement** : le cahier des charges propose un mot de passe temporaire généré par l'admin pour tous les comptes, avec matricule comme identifiant étudiant. Cela diffère de la décision prise précédemment (email obligatoire + lien d'invitation pour les étudiants). À valider avec l'IFAM avant d'implémenter l'authentification étudiant.

### Sécurité — règle d'or
- Un utilisateur ne doit jamais accéder à une information en modifiant un identifiant dans l'URL (contrôle systématique du périmètre de données, pas seulement du rôle)

---

## Phases de développement (planning officiel, section 32)

- [ ] **Phase 1** — Analyse des besoins et validation du cahier des charges (+ validation des points annexes ci-dessus avec l'IFAM)
- [ ] **Phase 2** — Modélisation UML et conception de la base de données
- [ ] **Phase 3** — Mise en place de la structure de l'application (Laravel/Livewire, rôles & permissions)
- [ ] **Phase 4** — Gestion des utilisateurs et des étudiants (+ page de contact)
- [ ] **Phase 5** — Formations, années de formation, sessions et inscriptions
- [ ] **Phase 6** — Modules, cours, planification et enseignants
- [ ] **Phase 7** — Évaluations, notes (+ workflow de modification) et présences
- [ ] **Phase 8** — Documents (administratifs + pédagogiques) et gestion financière
- [ ] **Phase 9** — Tableaux de bord, notifications et statistiques
- [ ] **Phase 10** — Tests, corrections et validation (recette par rôle)
- [ ] **Phase 11** — Mise en production, reprise des données existantes, formation des utilisateurs

### Repères pratiques pour l'ordre de dev (vu la priorité "espace étudiant + admin création utilisateurs" déjà donnée)
1. Phase 3 (structure + rôles) → indispensable avant tout
2. Phase 4, en commençant par : création d'utilisateur (admin) → dossier étudiant → page de contact
3. Maquettes Stitch : espace admin (création utilisateurs) en premier, comme déjà prévu
4. Une fois Phase 4 posée, enchaîner 5 → 6 → 7 → 8 → 9

---

## Documents et types (rappel)
- **Documents administratifs** : acte de naissance, pièce d'identité, diplôme, attestation, photo, autre — formats PDF/JPG/PNG, 10 Mo max
- **Documents pédagogiques** : support de cours, exercice, devoir, sujet, vidéo, autre
- **Relevé de notes simplifié PDF** : à inclure dès la v1 (peu coûteux, besoin immédiat)

## Notifications (déclencheurs)
Nouvelle note saisie · Décision sur demande de modification · Nouveau document pédagogique déposé · Nouveau paiement enregistré · Cours annulé/reporté · Compte créé/désactivé · **Nouveau message de contact reçu**

## Hors périmètre v1 (versions futures)
Paiement en ligne, application mobile dédiée, messagerie instantanée, notifications SMS/email automatisées, intégrations externes, statistiques avancées, gestion des examens/stages/diplômes.

---

## Notes pour Antigravity
- Utilise le projet Stitch **"IFAM Connect Design System"** comme référence visuelle exacte pour toutes les pages
- Respecte la charte graphique IFAM sur toutes les nouvelles pages (fond blanc, texte noir, accent rose/magenta pour les CTA)
- Les moyennes, la progression des modules et les situations financières sont **toujours calculées automatiquement**, jamais saisies à la main
- Les notes et présences confirmées ne sont **jamais modifiées directement** : toujours via le workflow de demande (ancienne valeur / nouvelle valeur / motif / validation admin)
- Le contrôle des droits d'accès doit se faire **côté serveur**, jamais uniquement via l'URL/l'identifiant affiché
- Ne pas oublier la **page de contact**, accessible depuis chaque espace utilisateur
- Toute nouvelle décision structurante doit être reportée dans `AGENTS.md`
