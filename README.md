# Omnipay: TBIBank

![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen.svg?style=flat)

**TBIBank eCommerce Platform integration for Omnipay payment processing library**

## Instalacja

```bash
composer require sylapi/omnipay-tbibank
```

## Konfiguracja

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('TBIBank');

// Dane dostępowe z TBI Integration Team
$gateway->setStoreId('tbitestapi_ro');           // Merchant code
$gateway->setUsername('tbitestapi');             // API username  
$gateway->setPassword('MZWlyiuAIbnyT0UO');       // API password
$gateway->setProviderCode('tbitestapi_ro');      // Provider code (same as store_id)
$gateway->setTestMode(true);                     // false dla produkcji

// Ścieżki do kluczy szyfrowania (otrzymane od TBI)
$gateway->setPublicKeyPath('/path/to/public.key');    // Do szyfrowania 
$gateway->setPrivateKeyPath('/path/to/private.key');  // Do odszyfrowywania callback'ów
```

## Podstawowy przykład aplikacji kredytowej

```php
$response = $gateway->purchase([
    'amount' => '1600.00',
    'transactionReference' => 'order#12345',
    'notifyUrl' => 'https://your-domain.com/tbi/callback',
    
    // Dane klienta (wymagane)
    'customerFirstName' => 'Jan',
    'customerLastName' => 'Kowalski', 
    'customerEmail' => 'jan@example.com',
    'customerPhone' => '0752000000',
    'customerCnp' => '1234567890123',
    
    // Adresy
    'billingAddress' => 'ul. Testowa 123',
    'billingCity' => 'Warszawa',
    'billingCounty' => 'Mazowieckie',
    
    // Opcje kredytu
    'instalments' => '24',
    
    // Produkty
    'items' => [
        [
            'name' => 'Smartwatch GPS',
            'qty' => '1.0000',
            'price' => 1600,
            'category' => '2',
            'sku' => 'WATCH001',
            'ImageLink' => 'https://shop.com/watch.jpg'
        ]
    ]
])->send();

if ($response->isSuccessful()) {
    if ($response->isRedirect()) {
        // Przekierowanie do TBI dla dalszego przetwarzania
        $response->redirect();
    }
    echo "Aplikacja kredytowa wysłana pomyślnie";
}
```

## Obsługa callback'ów (ReturnToProvider)

TBI wysyła callback'i ze statusem aplikacji:

```php
// W kontrollerze callback'a
$response = $gateway->completePurchase([
    'privateKeyPath' => '/path/to/private.key',
    'privateKeyPassword' => 'password' // jeśli wymagane
])->send();

if ($response->isSuccessful()) {
    // Kredyt zatwierdzony (status_id = 1)
    $orderId = $response->getTransactionId();
    echo "Kredyt zatwierdzony dla zamówienia: $orderId";
    
} elseif ($response->isCancelled()) {
    // Kredyt odrzucony/anulowany (status_id = 0)
    $orderId = $response->getTransactionId();
    $reason = $response->getRejectionReason();
    echo "Kredyt odrzucony dla $orderId: $reason";
}
```

## Anulowanie zamówienia (CanceledByCustomer)

Merchant może anulować zamówienie przed zatwierdzeniem:

```php
$response = $gateway->void([
    'transactionReference' => 'order#12345'
])->send();

if ($response->isSuccessful()) {
    echo "Zamówienie anulowane pomyślnie";
} else {
    echo "Błąd anulowania: " . $response->getMessage();
}
```

## Struktura danych

### Wymagane parametry aplikacji kredytowej

| Parametr | Typ | Opis |
|----------|-----|------|
| amount | string | Kwota zamówienia |
| transactionReference | string | Unikalny ID zamówienia |
| customerFirstName | string | Imię klienta |
| customerLastName | string | Nazwisko klienta |
| customerEmail | string | Email klienta |
| customerPhone | string | Telefon klienta |
| billingAddress | string | Adres rozliczeniowy |
| billingCity | string | Miasto rozliczeniowe |
| billingCounty | string | Województwo |
| instalments | string | Liczba rat (domyślnie 12) |
| items | array | Produkty w koszyku |

### Format produktów

```php
'items' => [
    [
        'name' => 'Nazwa produktu',
        'qty' => '1.0000', 
        'price' => 100.00,
        'category' => '1',     // Kategoria numeryczna
        'sku' => 'PROD001',
        'ImageLink' => 'https://...'
    ]
]
```

## Szyfrowanie

TBI wymaga szyfrowania RSA z podziałem na bloki:

1. **Klucz publiczny** - do szyfrowania żądań (plik `.pem`)
2. **Klucz prywatny** - do odszyfrowywania callback'ów (plik `.pem/.pfx`)

Klucze otrzymujesz od TBI Integration Team.

## Callback'i

TBI wysyła callback'i na `notifyUrl` z danymi:

```json
{
  "order_id": "145003523",
  "status_id": "1",        // 0=odrzucony, 1=zatwierdzony  
  "motiv": "Powód odrzucenia (jeśli status_id=0)"
}
```

## API Endpoints

| Environment | URL |
|-------------|-----|
| **Live** | `https://ecommerce.tbibank.ro/Api/LoanApplication` |

### Dostępne metody:

- `/Finalize` - Wysłanie aplikacji kredytowej
- `/CanceledByCustomer` - Anulowanie przez klienta

## Limitacje

❌ **Niedostępne funkcje:**
- `fetchTransaction()` - TBI nie oferuje API do sprawdzania statusu
- `refund()` - Zwroty wymagają kontaktu z TBI support

ℹ️ Status transakcji otrzymujesz wyłącznie przez callback'i.

## Komendy

| COMMAND | DESCRIPTION |
| ------ | ------ |
| `composer tests` | Testy jednostkowe |
| `composer phpstan` | Analiza statyczna PHPStan |

## Wsparcie

Dla wsparcia integracji skontaktuj się z **TBI Integration Team**.

---

## Struktura plików

```
src/
├── Gateway.php                 # Główna klasa bramy
├── Message/
│   ├── PurchaseRequest.php     # Wysyłanie aplikacji kredytowej  
│   ├── PurchaseResponse.php    # Odpowiedź z TBI
│   ├── CompletePurchaseRequest.php   # Obsługa callback'ów
│   ├── CompletePurchaseResponse.php  # Przetwarzanie statusu
│   ├── VoidRequest.php         # Anulowanie zamówienia
│   └── VoidResponse.php        # Odpowiedź anulowania
└── Trait/
    ├── Request.php             # Wspólne metody żądań + szyfrowanie
    └── Response.php            # Wspólne metody odpowiedzi
```