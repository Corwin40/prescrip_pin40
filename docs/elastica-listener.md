# Re-indexation Elasticsearch via Doctrine Event Listener

## Contexte

La recherche de bénéficiaires utilise **FOSElasticaBundle** pour interroger un index
Elasticsearch. Chaque `Beneficiary` y est indexé avec notamment son `structure.id`.

Le problème : quand une `Prescription` est supprimée, le bénéficiaire associé disparaît
de la recherche. Voir [FIXES.md](../FIXES.md) pour le diagnostic complet.

---

## Solution retenue : Doctrine Event Listener

Plutôt que de modifier le contrôleur, la synchronisation est gérée par un **listener**
qui réagit automatiquement à tout événement de suppression Doctrine.

### Fichiers concernés

```
src/EventListener/PrescriptionElasticaListener.php
config/services.yaml
```

---

## Le listener — `PrescriptionElasticaListener.php`

```php
class PrescriptionElasticaListener
{
    public function __construct(private ObjectPersisterInterface $beneficiaryPersister) {}

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Prescription) {
            return;
        }

        $beneficiary = $entity->getBeneficiaire();
        if ($beneficiary) {
            $this->beneficiaryPersister->replaceOne($beneficiary);
        }
    }
}
```

### Explication ligne par ligne

| Élément | Rôle |
|---|---|
| `ObjectPersisterInterface` | Service FOSElasticaBundle qui écrit dans un index Elasticsearch |
| `postRemove(PostRemoveEventArgs $args)` | Méthode appelée automatiquement par Doctrine après chaque `flush()` qui contient une suppression |
| `$args->getObject()` | Récupère l'entité qui vient d'être supprimée |
| `instanceof Prescription` | Filtre : on n'agit que si c'est une Prescription (le listener écoute tous les `postRemove`) |
| `getBeneficiaire()` | Récupère le bénéficiaire lié (toujours disponible en mémoire malgré la suppression de la prescription) |
| `replaceOne($beneficiary)` | Met à jour le document Elasticsearch du bénéficiaire avec ses données actuelles |

---

## L'enregistrement — `services.yaml`

```yaml
App\EventListener\PrescriptionElasticaListener:
    arguments:
        - '@fos_elastica.object_persister.beneficiary'
    tags:
        - { name: doctrine.event_listener, event: postRemove }
```

### Explication

| Clé | Valeur | Rôle |
|---|---|---|
| `arguments` | `@fos_elastica.object_persister.beneficiary` | Injecte le persister de l'index `beneficiary` défini dans `fos_elastica.yaml` |
| `tags.name` | `doctrine.event_listener` | Indique à Symfony que ce service doit être branché sur le bus d'événements Doctrine |
| `tags.event` | `postRemove` | Doctrine appellera la méthode `postRemove()` du listener après chaque suppression |

> Le nom de la méthode dans la classe (`postRemove`) doit correspondre exactement
> à la valeur de `event` dans le tag — c'est la convention Doctrine.

---

## Pourquoi cette approche est préférable

| Approche contrôleur | Approche listener |
|---|---|
| Logique Elasticsearch dans le contrôleur | Séparation des responsabilités |
| À dupliquer sur chaque action de suppression | Une seule fois, pour tous les chemins |
| Oubli possible sur une future route | Automatique, quel que soit l'appelant |

---

## Commande de secours

Si l'index est désynchronisé (ex. suppression directe en base), relancer la population complète :

```bash
php bin/console fos:elastica:populate --index=beneficiary
```
