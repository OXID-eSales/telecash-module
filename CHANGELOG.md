# Change Log for OXID eShop TeleCash Module

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [v1.0.0] - Unreleased

### Security

- Mask sensitive payment data (card numbers, IBAN, CVM, expiry dates) in SOAP request/response debug logs to comply with PCI-DSS requirements
- Mask sensitive fields (card data, approval codes, response hashes) in TeleCash Connect response debug logs
- Remove full HMAC hash values from debug logs, log only match/mismatch result
- Fix timing-attack vulnerability in HMAC hash comparison: use `hash_equals()` instead of `===`
- Use configured log level for Monolog handler instead of hardcoded DEBUG to prevent unintended sensitive data logging in production
- Fix unnecessary `|raw` in admin module config template: use `|e('html_attr')` for certificate file path value attribute
- Fix XSS in `createHiddenFormFields()`: apply `htmlspecialchars()` to field names and values before HTML rendering
- Fix missing admin authentication on `AdminTeleCashJsonEndpoint`: extend `AdminController` instead of `BaseController`
- Change credential module settings (shared secret, passwords) from type `str` to `password` for masked display in admin
- Add whitelist validation for SQL ORDER BY direction in `TeleCashOrderHistoryList` to prevent SQL injection
- Remove full SOAP XML from exception messages in `Error.php` and `Validation.php` to prevent information disclosure
- Deprecate misleading `getArrayRequestEscapedData()` method, delegate to `getArrayRequestData()`
- Add user ownership check when loading delivery address in `OrderController` to prevent IDOR
- Add explanatory comment for unavoidable `$_POST['cur']` manipulation in `CurrencyComponent`
- Remove automatic XDEBUG_SESSION_START parameter from sandbox mode URLs in `Context`
- Log database exceptions in `Order::updateDBField()` instead of silently swallowing them