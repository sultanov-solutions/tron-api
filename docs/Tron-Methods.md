Tron API — Complete Method Reference (English)

This consolidated guide documents the public API of `IEXBase\\TronAPI\\Tron`. Each entry includes a signature, parameter types, return types, possible exceptions, and a short example. Actual runtime behavior follows the implementation in `src/Tron.php`.

Conventions
- Address formats: Base58 (starts with `T`) or hex with `41` prefix. Unless noted, methods accept Base58 and convert to hex internally.
- Amounts: TRX amounts are expressed in TRX (not sun) unless otherwise specified.
- Exceptions: Most validation errors throw `IEXBase\\TronAPI\\Exception\\TronException`.

Getting Started (Laravel)
- Configure `config/tron-connection.php` and optionally `TRON_API_KEY` for TronGrid.
- Rebuild config cache after changes: `php artisan config:clear && php artisan config:cache`.

Construction

1) Tron::make
Signature:
```php
public static function make(
    ?HttpProviderInterface $fullNode = null,
    ?HttpProviderInterface $solidityNode = null,
    ?HttpProviderInterface $eventServer = null,
    ?HttpProviderInterface $signServer = null,
    string $privateKey = null
)
```
Description: Quick factory to build a Tron instance from provider objects and an optional private key.
Parameters: provider instances or null to use defaults; optional hex private key.
Returns: `static`
Throws: `TronException` on invalid parameters.
Example:
```php
$tron = \IEXBase\TronAPI\Tron::make();
```

2) Tron::init
Signature:
```php
public static function init(array $options = []): self
```
Description: Factory with rich options; normalizes empty/invalid endpoint URLs to network presets.
Options:
- network: `string` one of `mainnet|shasta|nile` (default `mainnet`).
- endpoints: `array{full_node:string,solidity_node:string,event_server:string,status_page?:string}`
- timeout_ms: `int` request timeout in ms (default 30000)
- headers: `array` custom HTTP headers
- api_key: `string` TronGrid key (sets `TRON-PRO-API-KEY`)
- auth: `array{basic?:[string,string], bearer?:string}`
- private_key: `string|null` hex private key
- address: `string|null` Base58 or hex address to set
Returns: `self`
Example:
```php
$tron = \IEXBase\TronAPI\Tron::init([
  'network' => 'mainnet',
  'endpoints' => ['full_node' => 'https://api.trongrid.io'],
  'api_key' => getenv('TRON_API_KEY') ?: null,
]);
```

Identity and Addresses

setPrivateKey
Signature:
```php
public function setPrivateKey(string $privateKey): void
```
Description: Sets the default hex private key used to sign transactions and derive the sender address.
Parameters:
- privateKey: `string` hex without `0x`.
Returns: `void`
Throws: `TronException` on invalid key.

setAddress
Signature:
```php
public function setAddress(string $address): void
```
Description: Sets the active address (Base58 or hex) used as default `from`.
Parameters:
- address: `string` Base58 (T...) or hex (41...)
Returns: `void`
Throws: `TronException` if address format is invalid.

getAddress
Signature:
```php
public function getAddress(): array
```
Description: Returns the current address in both encodings.
Returns: `array{base58:?string, hex:?string}`

validateAddress
Signature:
```php
public function validateAddress(string $address = null, bool $hex = false): array
```
Description: Validates address via node API.
Parameters:
- address: `?string` Base58 or hex. Defaults to the current address.
- hex: `bool` if true, treat `address` as hex.
Returns: `array` typically like `['result' => true]`.
Throws: `TronException`
Example:
```php
$ok = $tron->validateAddress('T...');
```

isAddress
Signature:
```php
public function isAddress(string $address = null): bool
```
Description: Local checksum validation of a Base58 Tron address.
Parameters: `address` `?string`
Returns: `bool`

createAccount
Signature:
```php
public function createAccount(): TronAddress
```
Description: Generates a new key pair and address (same as generateAddress).
Returns: `TronAddress` with `private_key`, `public_key`, `address_hex`, `address_base58`.

generateAddress
Signature:
```php
public function generateAddress(): TronAddress
```
Description: Generates a secp256k1 key pair and corresponding Tron address.
Returns: `TronAddress`

getAddressHex
Signature:
```php
public function getAddressHex(string $pubKeyBin): string
```
Description: Computes hex address (41...) from uncompressed public key bytes.
Parameters: `pubKeyBin: string` (65/64 bytes)
Returns: `string`

getBase58CheckAddress
Signature:
```php
public function getBase58CheckAddress(string $addressBin): string
```
Description: Encodes a 25‑byte binary address to Base58Check.
Returns: `string`

toUtf8
Signature:
```php
public function toUtf8($str): string
```
Description: Converts a hex string to raw UTF‑8 string.
Returns: `string`

Providers and Connectivity

providers
Signature:
```php
public function providers(): array
```
Description: Returns the currently configured provider instances (full/solidity/event/etc.).
Returns: `array`

isConnected
Signature:
```php
public function isConnected(): array
```
Description: Connectivity status per provider.
Returns: `array` e.g. `[['fullNode' => true], ...]`

setDefaultBlock / getDefaultBlock
Signatures:
```php
public function setDefaultBlock($blockID = false): void
public function getDefaultBlock()
```
Description: Set or get the default block reference used by some queries (`latest`, `earliest`, block number, or `false`).

Blocks and Transaction Queries

getCurrentBlock
```php
public function getCurrentBlock(): array
```
Returns the latest block (full node). Throws `TronException` on errors.

getBlock
```php
public function getBlock($block = null): array
```
Fetches a block by number, by hash (if hex string provided), or the current block when `latest`/`null`. Throws for missing/invalid identifiers.

getBlockByNumber
```php
public function getBlockByNumber(int $blockID): array
```
Validates non‑negative integer and returns the block; throws if not found.

getBlockByHash
```php
public function getBlockByHash(string $hashBlock): array
```
Returns a block by its id/hash.

getBlockTransactionCount
```php
public function getBlockTransactionCount($block): int
```
Returns the number of transactions in a block (0 if none).

getTransactionFromBlock
```php
public function getTransactionFromBlock($block = null, $index = 0)
```
Returns the transaction at `index` in a block. Throws on invalid index or when not found.

getTransaction
```php
public function getTransaction(string $transactionID): array
```
Returns transaction details by txID. Throws if missing.

getTransactionInfo
```php
public function getTransactionInfo(string $transactionID): array
```
Returns transaction receipt/fee info from solidity node.

getTransactionsToAddress / getTransactionsFromAddress / getTransactionsRelated
```php
public function getTransactionsToAddress(string $address, int $limit = 30, int $offset = 0)
public function getTransactionsFromAddress(string $address, int $limit = 30, int $offset = 0)
public function getTransactionsRelated(string $address, string $direction = 'to', int $limit = 30, int $offset = 0)
```
Lists incoming, outgoing, or both (via `direction`) transactions. Validates `direction in {to,from}`, `limit >= 0`, `offset >= 0`.

getBlockRange
```php
public function getBlockRange(int $start = 0, int $end = 30)
```
Returns a list of blocks from `start` to `end` (inclusive range handling per API). Validates inputs.

getLatestBlocks
```php
public function getLatestBlocks(int $limit = 1): array
```
Returns N latest blocks; `limit > 0` required.

Network‑wide Stats

getTransactionCount
```php
public function getTransactionCount(): int
```
Returns the total transaction count reported by the node.

Accounts, Balances, and Resources

getAccount
```php
public function getAccount(string $address = null): array
```
Returns account info (solidity node). Defaults to the current address when omitted.

getBalance
```php
public function getBalance(string $address = null, bool $fromTron = false): float
```
Returns TRX balance. When `fromTron = true`, converts from sun to TRX.

getTokenBalance (TRC10)
```php
public function getTokenBalance(int $tokenId, string $address, bool $fromTron = false)
```
Returns token balance or `0` if not present. Throws if token id not found in account assets.

getBandwidth
```php
public function getBandwidth(string $address = null)
```
Returns bandwidth info for an address.

getAccountResources
```php
public function getAccountResources(string $address = null)
```
Returns resource info (energy/bandwidth) for an address.

changeAccountName
```php
public function changeAccountName(string $address = null, string $account_name)
```
Changes account name. Can be done only once by protocol rules. Signs and broadcasts the transaction.

registerAccount
```php
public function registerAccount(string $address, string $newAccountAddress): array
```
Creates a new account using an already activated owner account.

Sending and Signing Transactions

send (alias) / sendTrx (alias)
```php
public function send(...$args): array
public function sendTrx(...$args): array
```
Both forward to `sendTransaction`.

sendTransaction (TRX)
```php
public function sendTransaction(string $to, float $amount, string $from = null, string $message = null): array
```
Builds, signs (requires `setPrivateKey`), broadcasts a TRX transfer. Returns merge of broadcast response and signed payload.

sendToken (TRC10, integer amount)
```php
public function sendToken(string $to, int $amount, string $tokenID, string $from = null)
```
Transfers TRC10 by integer `amount` (token units). Signs and broadcasts.

sendTokenTransaction (TRC10, TRX‑like amount)
```php
public function sendTokenTransaction(string $to, float $amount, int $tokenID = null, string $from = null): array
```
Transfers TRC10 using float `amount` which is internally converted by `toTron()`. Signs and broadcasts.

signTransaction
```php
public function signTransaction($transaction, string $message = null): array
```
Signs a transaction using the configured private key. Validates input, prevents double signing, optionally sets a UTF‑8 memo (`message`).
Throws when private key is missing or payload is invalid.

sendRawTransaction
```php
public function sendRawTransaction($signedTransaction): array
```
Broadcasts a signed transaction. Validates presence of `signature` array.

Smart Contracts and Tokens

contract (TRC20 helper)
```php
public function contract(string $contractAddress, string $abi = null)
```
Returns a `TRC20Contract` helper bound to the given address/ABI for common TRC20 interactions.

deployContract
```php
public function deployContract($abi, $bytecode, $feeLimit, $address, $callValue = 0, $bandwidthLimit = 0)
```
Deploys a smart contract. Validates `feeLimit <= 1_000_000_000` and payable constructor rules regarding `callValue`.

createToken (TRC10)
```php
public function createToken($token = [])
```
Creates a TRC10 token. Expects structured fields (owner_address, name, abbr, supply, timings, limits). Returns the create transaction result.

updateToken (TRC10)
```php
public function updateToken(
  string $description,
  string $url,
  int $freeBandwidth = 0,
  int $freeBandwidthLimit = 0,
  string $owner_address = null
)
```
Updates TRC10 metadata/bandwidth settings. Signs and broadcasts.

applyForSuperRepresentative
```php
public function applyForSuperRepresentative(string $address, string $url)
```
Applies for SR candidacy. Signs and broadcasts.

freezeBalance / unfreezeBalance / withdrawBlockRewards
```php
public function freezeBalance(float $amount = 0, int $duration = 3, string $resource = 'BANDWIDTH', string $owner_address = null)
public function unfreezeBalance(string $resource = 'BANDWIDTH', string $owner_address = null)
public function withdrawBlockRewards(string $owner_address = null)
```
Resource management helpers that build, sign, and broadcast corresponding transactions.

purchaseToken (TRC10)
```php
public function purchaseToken($issuerAddress, $tokenID, $amount, $buyer = null)
```
Purchases a TRC10 token from issuer. Signs and broadcasts.

Token and Network Listings

listTokens
```php
public function listTokens(int $limit = 0, int $offset = 0)
```
Lists TRC10 tokens. With `limit = 0` returns all; otherwise paginated. Validates `limit`/`offset`.

getTokensIssuedByAddress
```php
public function getTokensIssuedByAddress(string $address = null)
```
Lists TRC10 tokens issued by the address.

getTokenFromID / getTokenByID
```php
public function getTokenFromID($tokenID = null)
public function getTokenByID(string $token_id): array
```
Queries TRC10 token by name/id. Throws on invalid id type.

listSuperRepresentatives
```php
public function listSuperRepresentatives(): array
```
Lists SRs via `wallet/listwitnesses`.

listNodes
```php
public function listNodes(): array
```
Lists known nodes; returns host:port strings.

listExchanges
```php
public function listExchanges()
```
Lists exchanges from `/wallet/listexchanges`.

timeUntilNextVoteCycle
```php
public function timeUntilNextVoteCycle(): float
```
Returns seconds until the next SR vote maintenance window. Throws if node returns `-1`.

Contract/Event Queries

getEventResult
```php
public function getEventResult($contractAddress, int $sinceTimestamp = 0, string $eventName = null, int $blockNumber = 0)
```
Returns contract events matching filters. Requires event server provider. Validates logical dependencies: `eventName` requires `contractAddress`, `blockNumber` requires `eventName`.

getEventByTransactionID
```php
public function getEventByTransactionID(string $transactionID)
```
Returns events emitted within a given transaction ID. Requires event server provider.

Examples
- Check balance (TRX):
```php
$balance = $tron->getBalance('T...');
```
- Send TRX:
```php
$tron->setPrivateKey('HEX_PRIVATE_KEY');
$tx = $tron->sendTransaction('T_RECEIVER...', 1.5, null, 'thanks');
```
- Validate address:
```php
$isValid = $tron->isAddress('T...');
$viaNode = $tron->validateAddress('T...');
```
- Latest blocks:
```php
$blocks = $tron->getLatestBlocks(10);
```
