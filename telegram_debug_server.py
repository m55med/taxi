import requests

def get_btc_balance(address, testnet=False):
    if testnet:
        url = f"https://mempool.space/testnet/api/address/{address}"
    else:
        url = f"https://mempool.space/api/address/{address}"

    try:
        response = requests.get(url)
        data = response.json()
        balance_sats = data.get("chain_stats", {}).get("funded_txo_sum", 0) - data.get("chain_stats", {}).get("spent_txo_sum", 0)
        balance_btc = balance_sats / 1e8
        print(f"📬 Address: {address}")
        print(f"💰 Balance: {balance_btc} BTC")
    except Exception as e:
        print("❌ Error:", e)

# ✅ استخدم عنوانك هنا
address = "tb1qem90gx9zl2rjdzuddhpyksh9qtqhuq25vjz6hx"
get_btc_balance(address, testnet=True)
