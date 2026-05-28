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
- Accès au contenu des formations après achat (paiements fictifs)

### Interface d'administration
- Authentification sécurisée (accès restreint)
- **CRUD complet** sur les formations (création, lecture, modification, suppression)

---

## 🛠️ Stack technique

- **Backend** : PHP 8, Symfony 6, Doctrine ORM
- **Base de données** : MySQL
- **Frontend** : Twig, HTML/CSS
- **Sécurité** : Gestion des rôles et des accès via le composant Security de Symfony

---

## 📈 Architecture & bonnes pratiques

- Architecture **MVC** avec séparation claire des responsabilités
- Utilisation des **Form Types** Symfony pour la validation des données
- Gestion des accès par **rôles** (ROLE_USER / ROLE_ADMIN)
- Migrations de base de données avec **Doctrine Migrations**

---

## 🔮 Pistes d'évolution

Le projet a été pensé pour être évolutif. Parmi les fonctionnalités envisagées :
- Intégration d'un module de paiement en ligne (ex: Stripe)
- Tableau de bord utilisateur avec suivi de progression
- Gestion des utilisateurs par l'administrateur

modif 1