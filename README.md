# Omnipay: TBIBank

![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen.svg?style=flat)
![Tests](https://img.shields.io/badge/Tests-Passing-green.svg)
![Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)

**TBIBank eCommerce Platform integration for Omnipay payment processing library**

Biblioteka umożliwiająca integrację z platformą kredytową TBI Bank poprzez framework Omnipay. Obsługuje aplikacje kredytowe, callbacki oraz anulowanie zamówień z pełnym szyfrowaniem RSA.

## Instalacja

```bash
composer require sylapi/omnipay-tbibank
```

## Szybki start

```php
use Omnipay\Omnipay;

// Inicjalizacja bramy
$gateway = Omnipay::create('TBIBank');

// Konfiguracja (dane testowe)
$gateway->setStoreId('tbitestapi_ro');
$gateway->setUsername('tbitestapi'); 
$gateway->setPassword('MZWlyiuAIbnyT0UO');
$gateway->setProviderCode('tbitestapi_ro');
$gateway->setTestMode(true);

// Aplikacja kredytowa
$response = $gateway->purchase([
    'amount' => '2500.00',
    'transactionReference' => 'order#12345',
    'customerFirstName' => 'Catalin',
    'customerLastName' => 'Test',
    'customerEmail' => 'test@example.com'
])->send();

if ($response->isSuccessful() && $response->isRedirect()) {
    // Przekieruj klienta na platformę TBI
    header('Location: ' . $response->getRedirectUrl());
}
```

## 🔄 Przepływ pracy (Flow)

### 1. Aplikacja kredytowa

```
[E-commerce] → [TBI API] → [TBI Portal] → [Klient] → [Callback] → [E-commerce]
```

**Krok 1**: Sklep wysyła zaszyfrowane dane aplikacji kredytowej
**Krok 2**: TBI API zwraca URL przekierowania (301/302)
**Krok 3**: Klient kończy aplikację na portalu TBI
**Krok 4**: TBI wysyła callback'a z wynikiem (zatwierdzenie/odrzucenie)

### 2. Detailowy flow

1. **Przygotowanie danych**
   - Dane zamówienia i klienta
   - Szyfrowanie RSA (danych wrażliwych)
   - Wysłanie POST do `/Api/LoanApplication/Finalize`

2. **Odpowiedź TBI**
   - HTTP 301/302 z URL przekierowania
   - Klient trafia na portal TBI Bank

3. **Portal TBI**
   - Weryfikacja tożsamości klienta
   - Ocena zdolności kredytowej
   - Akceptacja/odrzucenie przez klienta

4. **Callback**
   - TBI wywołuje `notifyUrl` z rezultatem
   - Dane są opcjonalnie zaszyfrowane
   - Status: approved/rejected/cancelled

## Konfiguracja

### Środowisko testowe

```php
$gateway->setStoreId('tbitestapi_ro');
$gateway->setUsername('tbitestapi');
$gateway->setPassword('MZWlyiuAIbnyT0UO');
$gateway->setProviderCode('tbitestapi_ro');
$gateway->setTestMode(true);
```

### Środowisko produkcyjne

```php
$gateway->setStoreId('your_store_id');
$gateway->setUsername('your_username');
$gateway->setPassword('your_password');
$gateway->setProviderCode('your_store_id');
$gateway->setTestMode(false);

// Własny klucz publiczny do szyfrowania (bezpośrednia zawartość)
$gateway->setPublicKey('-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...
-----END PUBLIC KEY-----');
```

## 💳 Aplikacja kredytowa

### Podstawowy przykład

```php
$response = $gateway->purchase([
    'amount' => '2500.00',
    'transactionReference' => 'order#' . uniqid(),
    'description' => 'Smartwatch order',
    'notifyUrl' => 'https://your-domain.com/tbi/callback',
    
    // Dane klienta (wymagane)
    'customerFirstName' => 'Catalin',
    'customerLastName' => 'Test',
    'customerEmail' => 'test@example.com',
    'phone' => '0700000000',
    'customerCnp' => '',  // CNP może być pusty w testach
    
    // Adres rozliczeniowy
    'billingAddress' => 'Strada Test 123',
    'billingCity' => 'Bucuresti', 
    'billingCounty' => 'Bucuresti',
    
    // Produkty w koszyku
    'items' => [
        [
            'name' => 'Ceas smartwatch Polar Vantage V',
            'qty' => '1.0000',
            'price' => 2500.00,
            'category' => '8',
            'sku' => 'WATCH001',
            'ImageLink' => 'https://example.com/image.jpg'
        ]
    ]
])->send();

// Sprawdź rezultat
if ($response->isSuccessful()) {
    if ($response->isRedirect()) {
        // Przekieruj klienta na portal TBI
        $redirectUrl = $response->getRedirectUrl();
        header("Location: $redirectUrl");
        exit;
    }
} else {
    // Obsłuż błąd
    echo "Błąd: " . $response->getMessage();
}
```

### Wymagane parametry

| Parametr | Typ | Opis |
|----------|-----|------|
| `amount` | string | Kwota zamówienia (format: "1600.00") |
| `transactionReference` | string | Unikalny ID zamówienia |
| `customerFirstName` | string | Imię klienta |
| `customerLastName` | string | Nazwisko klienta |
| `customerEmail` | string | Email klienta |
| `phone` | string | Telefon klienta |
| `notifyUrl` | string | URL callback'a |

### Opcjonalne parametry

| Parametr | Typ | Opis |
|----------|-----|------|
| `description` | string | Opis zamówienia |
| `customerCnp` | string | CNP (Romanian Personal Code) |
| `billingAddress` | string | Adres rozliczeniowy |
| `billingCity` | string | Miasto |
| `billingCounty` | string | Województwo/Kraj |
| `items` | array | Lista produktów |

## 📞 Obsługa callback'ów

Callback'i są wysyłane przez TBI po zakończeniu procesu aplikacji kredytowej.

```php
// Endpoint callback'a: /tbi/callback
$response = $gateway->completePurchase([
    // TYLKO jeśli klucz prywatny ma hasło:
    // 'privateKeyPassword' => 'haslo_do_klucza'
])->send();

if ($response->isSuccessful()) {
    // Kredyt zatwierdzony
    $orderId = $response->getTransactionId();
    echo "Kredyt zatwierdzony dla zamówienia: $orderId";
    
    // Zaktualizuj status w bazie danych
    // updateOrderStatus($orderId, 'approved');
    
} elseif ($response->isCancelled()) {
    // Kredyt odrzucony/anulowany
    $orderId = $response->getTransactionId();
    $reason = $response->getRejectionReason();
    echo "Kredyt odrzucony: $reason";
    
    // updateOrderStatus($orderId, 'rejected', $reason);
}
```

## ❌ Anulowanie zamówień

Możliwe przed zatwierdzeniem kredytu przez klienta.

```php
$response = $gateway->void([
    'transactionReference' => 'order#12345'
])->send();

if ($response->isSuccessful()) {
    echo 'Zamówienie zostało anulowane';
} else {
    echo 'Błąd anulowania: ' . $response->getMessage();
}
```

## 🔐 Szyfrowanie RSA

### Wbudowany klucz testowy

W trybie testowym używany jest wbudowany klucz publiczny TBI:

```php
$gateway->setTestMode(true); // Automatycznie użyje klucza testowego TBI
```

### Własny klucz produkcyjny

```php
// Klucz publiczny (zawartość klucza bezpośrednio)
$gateway->setPublicKey('-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...
-----END PUBLIC KEY-----');

// Klucz prywatny do callback'ów (zawartość klucza bezpośrednio)
$gateway->setPrivateKey('-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC...
-----END PRIVATE KEY-----');

// TYLKO jeśli klucz prywatny MA HASŁO (rzadko potrzebne)
// $gateway->setPrivateKeyPassword('haslo_ktorym_zaszyfrowano_klucz');
```

### Format kluczy

Klucze muszą być w formacie PEM:

```
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...
-----END PUBLIC KEY-----
```

### Hasło do klucza prywatnego

**Uwaga**: Hasło (`setPrivateKeyPassword`) jest potrzebne **TYLKO** gdy klucz prywatny został wygenerowany z hasłem ochronnym.

```bash
# Klucz BEZ hasła (częściej używane przez aplikacje):
openssl genrsa -out private.pem 2048

# Klucz Z hasłem (rzadziej):  
openssl genrsa -aes256 -out private.pem 2048
```

Jeśli twój klucz nie ma hasła, **nie ustawiaj** `setPrivateKeyPassword`.

## 🌐 Endpointy API

### Produkcja
- **Aplikacja kredytowa**: `https://ecommerce.tbibank.ro/Api/LoanApplication/Finalize`
- **Anulowanie zamówienia**: `https://ecommerce.tbibank.ro/Api/LoanApplication/CanceledByCustomer`

### Metody HTTP
- **POST** - wysłanie aplikacji kredytowej
- **POST** - anulowanie zamówienia
- **POST/GET** - callback (ustaw w panelu TBI)

## 📊 Struktura danych

### Dane zamówienia (przed szyfrowaniem)

```json
{
    "store_id": "tbitestapi_ro",
    "order_id": "order#12345", 
    "back_ref": "https://your-domain.com/callback",
    "order_total": "2500.00",
    "username": "tbitestapi",
    "password": "MZWlyiuAIbnyT0UO",
    "customer": {
        "fname": "Catalin",
        "lname": "Test",
        "email": "test@example.com", 
        "phone": "0700000000",
        "cnp": "",
        "billing_address": "Strada Test 123",
        "billing_city": "Bucuresti",
        "billing_county": "Bucuresti",
        "promo": 0
    },
    "items": [
        {
            "name": "Product name",
            "qty": "1.0000", 
            "price": 2500.00,
            "category": "8",
            "sku": "PRODUCT001",
            "ImageLink": "https://example.com/image.jpg"
        }
    ]
}
```

### Odpowiedź TBI (sukces)

```http
HTTP/1.1 301 Moved Permanently
Location: https://app.tbibank.ro/b2cportal/platform/B2CAuth/ResumeJourney?journeyName=FTOS_Loan_eCommerce&sessionId=xxx&link2payid=yyy
```

### Callback data

```json
{
    "status": "approved|rejected|cancelled",
    "order_id": "order#12345", 
    "amount": "2500.00",
    "reason": "rejection reason (if applicable)"
}
```

## 🧪 Testowanie

### Uruchomienie testów

```bash
vendor/bin/phpunit
```

### PhpStan (Level 5)

```bash
vendor/bin/phpstan analyse
```

## ⚠️ Wymagania

- **PHP**: 8.0+
- **PHP Extensions**: openssl, curl, json
- **Dependencies**: omnipay/common ^3.0

## 🔧 Configuration checklist

### Pre-deployment

- [ ] Otrzymaj credentials od TBI Integration Team
- [ ] Skonfiguruj klucze RSA (publiczny/prywatny)
- [ ] Ustaw callback URL w panelu TBI
- [ ] Przetestuj w środowisku testowym
- [ ] Skonfiguruj logowanie błędów

### Go-live

- [ ] `setTestMode(false)`
- [ ] Zmień credentials na produkcyjne
- [ ] Ustaw właściwą ścieżkę do kluczy
- [ ] Skonfiguruj monitoring callback'ów
- [ ] Testuj z prawdziwymi danymi (tylko małe kwoty!)

## 🆘 Troubleshooting

### HTTP 500 Error
- Sprawdź credentials
- Zweryfikuj format danych
- Sprawdź klucz publiczny

### HTTP 301/302 to błędny URL
- Skonfiguruj prawidłowy `notifyUrl`
- Sprawdź konfigurację w panelu TBI

### Callback nie działa
- Zweryfikuj endpoint callback'a
- Sprawdź klucz prywatny do odszyfrowywania
- Sprawdź logi serwera

### Błędy szyfrowania
- Sprawdź format klucza (PEM)
- Zweryfikuj uprawnienia do plików kluczy
- Testuj z wbudowanym kluczem testowym

## 📞 Wsparcie

W przypadku problemów technicznych skontaktuj się z:
- **GitHub Issues**: [sylapi/omnipay-tbibank/issues](https://github.com/sylapi/omnipay-tbibank/issues)

---

## Licencja

MIT License. Zobacz [LICENSE](LICENSE) aby uzyskać więcej informacji.