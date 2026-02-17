# Omnipay: TBIBank

![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen.svg?style=flat)

## Instalacja

```bash
composer require sylapi/omnipay-tbibank
```

## Konfiguracja

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('TBIBank');
$gateway->setApiKey('your-api-key');
$gateway->setSecretKey('your-secret-key');
$gateway->setTestMode(true); // false dla produkcji
```

## Podstawowy przykład płatności

```php
// TODO: Implementuj zgodnie z dokumentacją TBIBank API
$response = $gateway->purchase([
    'amount' => '100.00',
    'currency' => 'GEL',
    'transactionReference' => 'order#12345',
    'description' => 'Payment description',
    'returnUrl' => 'https://your-site.com/success',
    'cancelUrl' => 'https://your-site.com/cancel',
    'notifyUrl' => 'https://your-site.com/notify',
])->send();

if ($response->isSuccessful()) {
    if ($response->isRedirect()) {
        $response->redirect();
    }
}
```

## Podstawowy przykład finalizacji

```php
// TODO: Implementuj zgodnie z dokumentacją TBIBank API
$response = $gateway->completePurchase([
    'transactionReference' => 'order#12345',
])->send();

if ($response->isSuccessful()) {
    // Płatność zakończona sukcesem
}
```

## Podstawowy przykład zwrotu

```php
// TODO: Implementuj zgodnie z dokumentacją TBIBank API
$response = $gateway->refund([
    'transactionReference' => 'order#12345',
    'amount' => '50.00',
])->send();

if ($response->isSuccessful()) {
    // Zwrot wykonany pomyślnie
}
```

## Podstawowy przykład anulowania

```php
// TODO: Implementuj zgodnie z dokumentacją TBIBank API
$response = $gateway->void([
    'transactionReference' => 'order#12345',
])->send();

if ($response->isSuccessful()) {
    // Płatność anulowana
}
```

## Podstawowy przykład sprawdzania statusu transakcji

```php
// TODO: Implementuj zgodnie z dokumentacją TBIBank API
$response = $gateway->fetchTransaction([
    'transactionReference' => 'order#12345',
])->send();

if ($response->isSuccessful()) {
    $status = $response->getStatus();
    // Przetwórz status transakcji
}
```

## Komendy

| COMMAND | DESCRIPTION |
| ------ | ------ |
| composer tests | Tests |
| composer phpstan |  PHPStan |

## TODO

1. Zaimplementuj właściwe endpointy API zgodnie z dokumentacją TBIBank
2. Dostosuj strukturę danych requestów i responsów
3. Zaimplementuj właściwą logikę autoryzacji/podpisu
4. Przetestuj integrację z sandbox TBIBank
5. Dodaj obsługę wszystkich wymaganych pól i walidację
6. Zaktualizuj testy jednostkowe