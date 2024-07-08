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
http://localhost/api/docs?ui=re_doc

## Schematy


### `POST  /api/{version}/purchase`
### Request:
`Content-type: application/json`

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
Dodam jeszcze, żeby utworzenie encji Purchase się powiodło musi być wystarczająca wartość quantity w encji Product względem zamówienia.

### Response:
`Content-type: application/json`

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


### `GET  /api/{version}/purchase/{id}`

### Response:
`Content-type: application/json`

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


### `GET  /api/{version}/purchase/{id}/details`

### Response:
`Content-type: application/json`

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