
### SEP-0002 - Federation

This examples shows how to resolve a stellar address, a stellar account id, a transaction id and a forward by using the federation protocol. For more details see: [SEP-0002 Federation](https://github.com/stellar/stellar-protocol/blob/master/ecosystem/sep-0002.md).

#### Resolving a stellar address

To resolve a stellar address like for example ```bob*soneso.com``` we can use the static method  ```Federation::resolveStellarAddress ```  as shown below:

```php
$response = Federation::resolveStellarAddress("bob*soneso.com");

print($response->getStellarAddress() . PHP_EOL);
// bob*soneso.com

print($response->getAccountId() . PHP_EOL);
// GBVPKXWMAB3FIUJB6T7LF66DABKKA2ZHRHDOQZ25GBAEFZVHTBPJNOJI

print($response->getMemoType() . PHP_EOL);
// text

print($response->getMemo() . PHP_EOL);
// hello memo text
```

#### Resolving a stellar account id

To resolve a stellar account id like for example ```GBVPKXWMAB3FIUJB6T7LF66DABKKA2ZHRHDOQZ25GBAEFZVHTBPJNOJI``` we can use the static method  ```Federation::resolveStellarAccountId ```. We need to provide the account id and the federation server url as parameters:

```php
$response = Federation::resolveStellarAccountId("GBVPKXWMAB3FIUJB6T7LF66DABKKA2ZHRHDOQZ25GBAEFZVHTBPJNOJI", "https://stellarid.io/federation");

print($response->getStellarAddress() . PHP_EOL);
// bob*soneso.com

print($response->getAccountId() . PHP_EOL);
// GBVPKXWMAB3FIUJB6T7LF66DABKKA2ZHRHDOQZ25GBAEFZVHTBPJNOJI

print($response->getMemoType() . PHP_EOL);
// text

print($response->getMemo() . PHP_EOL);
// hello memo text
```

#### Resolving a stellar transaction id

To resolve a stellar transaction id like for example ```c1b368c00e9852351361e07cc58c54277e7a6366580044ab152b8db9cd8ec52a``` we can use the static method  ```Federation::resolveStellarTransactionId ```.  We need to provide the transaction id and the federation server url as parameters:

```php
// Returns the federation record of the sender of the transaction if known by the server
$response = Federation::resolveStellarTransactionId("c1b368c00e9852351361e07cc58c54277e7a6366580044ab152b8db9cd8ec52a", "https://stellarid.io/federation");
```

#### Resolving a forward

Used for forwarding the payment on to a different network or different financial institution. Here we can use the static method  ```Federation.resolveForward``` . We need to provide the needed query parameters as ```array[String => String]``` and the federation server url:

```php
$response = Federation::resolveForward([
"forward_type" => "bank_account",
"swift" => "BOPBPHMM",
"acct" => "2382376"], 
"https://stellarid.io/federation");

// resulting request url: 
// https://stellarid.io/federation?type=forward&forward_type=bank_account&swift=BOPBPHMM&acct=2382376
```