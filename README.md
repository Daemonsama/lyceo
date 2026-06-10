# SPCFormation - Plateforme de formations en ligne

## 🎯 Contexte du projet

Projet réalisé dans le cadre de ma formation, en conditions réelles pour une entreprise cliente. L'objectif était de concevoir et développer une plateforme web complète permettant la vente et la consultation de formations en ligne.

---

## 🔍 Présentation

**SPCFormation** est une application web full-stack développée avec **Symfony** permettant à une entreprise de proposer son catalogue de formations en ligne. Le projet couvre aussi bien l'expérience utilisateur (consultation, inscription) que la gestion back-office via une interface d'administration dédiée.

---

## ⚙️ Fonctionnalités développées

### Interface utilisateur
- Inscription et authentification des utilisateurs
- Consultation du catalogue de formations
- Visualisation du détail de chaque formation
- Achat des formations via **Stripe Checkout** (carte bancaire)
- Accès au contenu des formations après paiement confirmé
- Téléchargement d'attestation PDF à la fin de formation

### Interface d'administration
- Authentification sécurisée (accès restreint)
- **CRUD complet** sur les formations (création, lecture, modification, suppression)
- **Codes promo / coupons** par formation (réduction % ou €, sync Stripe)
- Tableau de bord **Paiements** et **Statistiques** (dont nombre de codes promo actifs)

---

## 🛠️ Stack technique

- **Backend** : PHP 8, Symfony 6, Doctrine ORM
- **Base de données** : MySQL
- **Frontend** : Twig, HTML/CSS
- **Sécurité** : Gestion des rôles et des accès via le composant Security de Symfony
- **Paiement** : [Stripe](https://stripe.com) (Checkout Session + webhook)

---

## 💳 Configuration Stripe

1. Créez un compte sur [dashboard.stripe.com](https://dashboard.stripe.com) et récupérez vos clés de test.
2. Ajoutez dans `.env.local` (ou `.env.dev`) :

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

3. En local, pour recevoir les webhooks :

```bash
stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
```

Copiez le secret `whsec_...` affiché par la CLI dans `STRIPE_WEBHOOK_SECRET`.

4. Appliquez la migration des codes promo :

```bash
php bin/console doctrine:migrations:migrate
```

5. En production, créez un endpoint webhook dans le dashboard Stripe pointant vers `https://votre-domaine/stripe/webhook` (événement `checkout.session.completed`).

Le flux : l'utilisateur clique sur « Acheter » → redirection Stripe → retour sur `/formation/{id}/payment/success` avec vérification de la session. Le webhook confirme aussi l'achat si l'utilisateur ferme le navigateur avant le retour.

### Codes promo (bons de réduction)

1. Dans l'admin : **Formations → créer / modifier** → section **Codes promo**.
2. Ajoutez un code (ex. `LYCEO10`), une réduction en **%** ou en **€**, puis enregistrez.
3. Le coupon est créé automatiquement dans Stripe et ne fonctionne **que pour cette formation**.
4. L'utilisateur le saisit sur la fiche formation avant d'acheter.

Synchronisation manuelle si besoin :

```bash
php bin/console app:stripe:sync-promo-codes
```

---

## 📈 Architecture & bonnes pratiques

- Architecture **MVC** avec séparation claire des responsabilités
- Utilisation des **Form Types** Symfony pour la validation des données
- Gestion des accès par **rôles** (ROLE_USER / ROLE_ADMIN)
- Migrations de base de données avec **Doctrine Migrations**

---

## 🔮 Pistes d'évolution

Le projet a été pensé pour être évolutif. Parmi les fonctionnalités envisagées :
- Gestion des utilisateurs par l'administrateur

