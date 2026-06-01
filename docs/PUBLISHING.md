# Publier `rodiumai/laravel-sdk`

Guide pas à pas pour GitHub + Packagist.

## Nom du dépôt GitHub (recommandé)

| Élément | Valeur recommandée |
|---------|-------------------|
| **Nom du repo** | `rodiumai-laravel-sdk` |
| **Organisation (officiel)** | `https://github.com/rodiumai/rodiumai-laravel-sdk` |
| **Dépôt actuel** | `https://github.com/lecodeur228/rodiumai-laravel-sdk` |

Le nom **`rodiumai-laravel-sdk`** est le standard : il correspond au package Composer `rodiumai/laravel-sdk` et évite la confusion avec d’autres SDK.

> Le nom Packagist est défini dans `composer.json` → `"name": "rodiumai/laravel-sdk"`.  
> Le nom du repo GitHub peut différer, mais **`rodiumai-laravel-sdk`** reste le plus clair.

### Namespace Packagist `rodiumai`

Pour publier sous **`rodiumai/laravel-sdk`**, tu dois pouvoir prouver que tu contrôles le vendor `rodiumai` sur Packagist :

- **Option A (idéal)** — Créer l’organisation GitHub **`rodiumai`**, y pousser le repo, lier Packagist à cette org.
- **Option B** — Compte Packagist personnel + première soumission ; Packagist peut demander une validation du vendor.
Ce dépôt est déjà configuré pour **`rodiumai/laravel-sdk`** sur Packagist.

---

## Checklist avant le premier push

- [ ] Aucun fichier `.env` ou clé API dans le commit (`git status` ne doit pas lister `.env`)
- [ ] `composer test` passe en local
- [ ] `CHANGELOG.md` à jour pour `v0.1.0`
- [ ] README et badges pointent vers la bonne URL GitHub (après création du repo)

---

## 1. Créer le dépôt GitHub

1. Va sur [github.com/new](https://github.com/new)
2. **Repository name** : `rodiumai-laravel-sdk`
3. Description : `Official PHP / Laravel SDK for the Rodium AI API`
4. Public, **sans** README / .gitignore (déjà dans le projet)
5. Créer le repo

---

## 2. Premier commit et push

Depuis le dossier du package :

```bash
cd rodiumai-laravel-sdk

git init
git add .
git status   # vérifier : pas de .env, pas de vendor/

git commit -m "feat: initial release v0.1.0 — Rodium AI Laravel SDK"

git branch -M main
git remote add origin https://github.com/lecodeur228/rodiumai-laravel-sdk.git

git push -u origin main
```

Si `vendor/` apparaît, vérifie que `.gitignore` est bien pris en compte.

---

## 3. Tag de version (SemVer)

Packagist utilise les **tags Git** pour les versions :

```bash
git tag -a v0.1.0 -m "First stable SDK release"
git push origin v0.1.0
```

Installable ensuite via :

```bash
composer require rodiumai/laravel-sdk:^0.1
```

---

## 4. Packagist (obligatoire pour `composer require` sans config VCS)

Sans cette étape, Composer affiche : *Could not find a matching version of package rodiumai/laravel-sdk*.

1. Compte sur [packagist.org](https://packagist.org) (connexion GitHub recommandée)
2. **Submit** → URL du repo :  
   `https://github.com/lecodeur228/rodiumai-laravel-sdk`
3. Packagist lit `composer.json` → le package apparaît comme **`rodiumai/laravel-sdk`**
4. Vérifier que le tag **`v0.1.0`** est visible sur Packagist (onglet Versions)
5. **Settings du package** → activer le webhook GitHub (auto-update à chaque push/tag)

Test après indexation (1–2 min) :

```bash
composer show rodiumai/laravel-sdk --all
```

Webhook Packagist (si config manuelle) :  
`https://packagist.org/api/github?username=TON_USER_PACKAGIST`

---

## 5. Mettre à jour les URLs (changement de dépôt)

Si tu déplaces le repo vers l’organisation **`rodiumai`**, mets à jour dans :

- `composer.json` → `support.issues` et `support.source`
- `README.md` → badge CI GitHub Actions

Puis commit + push.

---

## 6. Releases GitHub (optionnel mais pro)

Sur GitHub → **Releases** → **Draft new release** :

- Tag : `v0.1.0`
- Titre : `v0.1.0 — First release`
- Description : copier la section `[0.1.0]` du `CHANGELOG.md`

---

## 7. Après publication

```bash
composer require rodiumai/laravel-sdk
```

Documenter sur [rodiumai.io/docs](https://www.rodiumai.io/docs) que le SDK PHP/Laravel natif est disponible (remplace « coming soon »).

### Versions suivantes

1. Développer sur une branche / PR
2. Mettre à jour `CHANGELOG.md` sous `[Unreleased]`
3. `composer test`
4. Tag `v0.1.1`, `v0.2.0`, etc.
5. Packagist se met à jour via webhook

---

## Commandes utiles

| Action | Commande |
|--------|----------|
| Tests | `composer test` |
| Smoke test API | `RODIUMAI_API_KEY=… php bin/smoke-test.php` |
| Régénérer enum modèles | `RODIUMAI_API_KEY=… php bin/generate-model-enum.php` |
