<?php

require "./vendor/autoload.php";
use Web3p\EthereumTx\Transaction as Tx;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Web3;

$RPC = 'https://rinkeby.infura.io/v3/fde4edab65a94a2aa7dae9f9fe660090';
$provider = new HttpProvider(new HttpRequestManager($RPC));
$web3 = new Web3($provider);
$PRIVATE_KEY = '17927af46607a9b8d48129122b5a74887e7ed7de76935873de7e3b6673038d18';
$nft = '0x7D4C854C66352F3892Fb7359c86016CAbaF01A40';
$abi = '[{"inputs": [{"internalType": "address","name": "minter","type": "address"},{"internalType": "string","name": "uri","type": "string"}],"name": "mint","outputs": [{"internalType": "uint256","name": "tokenId","type": "uint256"}],"stateMutability": "nonpayable","type": "function"}]';
$contract = new Contract($provider, $abi);
$minter = '0x64Ae236b229496a03E80C0500e5D37da5ba1eC30';
$sender = '0x0afb6E5258173a11Ca67EcBfF0Fd8cc3bb418765';

$web3->eth->getTransactionCount($sender, function ($err, $result)
     use ($minter, $contract, $nft, $web3, $PRIVATE_KEY) {
        if ($err !== null) {
            throw $err;
        }
        $nonce = $result;
        $data = $contract->at($nft)->getData(
            'mint',
            $minter,
            '/ipfs/QmWLsBu6nS4ovaHbGAXprD1qEssJu4r5taQfB74sCG51tp'
        );
        $tx = new Tx([
            "nonce" => '0x' . (preg_match('/^([0-9]{1})$/', $nonce) ? '0x' . $nonce : $web3->utils->toHex($nonce)),
            "gasPrice" => '0x' . $web3->utils->toHex(1000000000),
            "gasLimit" => '0x' . $web3->utils->toHex(500000),
            "to" => $nft,
            "value" => '0x00',
            "data" => '0x' . $data,
            "chainId" => 4,
        ]);
        $serializeSignedTx = $tx->sign($PRIVATE_KEY);
        $web3->eth->sendRawTransaction(
            '0x' . $serializeSignedTx,
            function ($err, $transaction) {
                if ($err !== null) {
                    throw $err;
                }
                echo $transaction;
            }
        );
    });
