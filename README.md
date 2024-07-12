## Wprowadzenie

Napisałem API w dwóch wersjach:
- `v1` - z wykorzystaniem API Platform
- `v2` - z użyciem kontrolera

API posiada 3 endpointy:
- `GET  /api/{version}/purchase/{purchaseId}`
- `GET  /api/{version}/purchase/{purchaseId}/details` - rozwiązanie 6 zadania
- `POST /api/{version}/purchase`

`{version}` - `v1` lub `v2`

oba API przyjmują te same dane, tak samo zwracają dane i zachowują sie podobnie jeżeli chodzi o efekt końcowy

Schematy dla requestów i response można sprawdzić pod adresem:
http://localhost:8080/api/docs?ui=re_doc

Wykorzystane narzędzia:
`PhpStorm`, `Docker`, `Postman`, `PHP CS Fixer`, `PHPStan`, `PHPUnit`, `API Platform`

## Instalacja

Należy utworzyć obraz z pliku ```docker-compose.yaml```

Instalacja
```
composer install
```

Tworzenie bazy danych
```
php bin/console doctrine:database:create
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate
```

Ładowanie testowych danych
```
php bin/console doctrine:fixtures:load
```

## Endpoints

### `POST  /api/{version}/purchase`
Tworzy nowe zamówienie
### Request:
header
```
Content-type: application/json
Authorization: Bearer test_apiToken
```
body
```json
{
  "name": "string",
  "email": "user@example.com",
  "items": [
    {
      "productId": 1,
      "quantity": 1
    }
  ]
}
```

---

### Response:
`Content-type: application/json` `200`

```json
{
  "id": 0,
  "items": [
    {
      "productId": 1,
      "quantity": 1,
      "unitPrice": "1.00"
    }
  ]
}
```

`Content-type: application/json` `400`

```json
{
  "error": "Bad request"
}
```
Odpowiedź zwracana w przypadku błędnego zapytania, może to być np. ujemna wartość dla quantity

`Content-type: application/json` `401`

```json
{
  "error": "Invalid Api Token"
//  "error": "Full authentication is required to access this resource."
}
```

Odpowiedź zwracana w przypadku podania nieprawidłowego ApiTokenu bądź gdy nie podano go wcale

`Content-type: application/json` `415`

```json
{
  "error": "Unsupported media type"
}
```
Odpowiedź zwracana w przypadku użycia innej wartości niż `application/json` dla parametru `Content-type`

`Content-type: application/json` `422`

```json
{
  "error": "Failed to create purchase"
//  "error": "Not found product"
//  "error": "Not enough product quantity available"
}
```
Odpowiedź zwracana w przypadku podania błędnego id produktu, gdy wartość quantity w encji produktu
jest niewystarczająca, w przypadku problemów ze spójnością danych w bazie.


---

### `GET  /api/{version}/purchase/{id}`
Zwraca informacje o zamówieniu
### Response:
`Content-type: application/json` `200`

```json
{
  "id": 0,
  "items": [
    {
      "productId": 1,
      "quantity": 1,
      "unitPrice": "1.00"
    }
  ]
}
```

`Content-type: application/json` `404`

```json
{
  "error": "Not found purchase"
}
```

---

### `GET  /api/{version}/purchase/{id}/details`
Zwraca informacje o zamówieniu wraz z informacją o cenie całkowitej, VAT i sumie przedmiotów
### Response:
`Content-type: application/json` `200`

```json
{
  "id": 0,
  "items": [
    {
      "productId": 1,
      "quantity": 2,
      "unitPrice": "1.00"
    }
  ],
  "total": {
    "price": "2.00",
    "vat": "0.46",
    "quantity": 2
  }
}
```

`Content-type: application/json` `404`

```json
{
  "error": "Not found purchase"
}
```
