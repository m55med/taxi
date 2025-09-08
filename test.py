# حفظ هذا الملف مثلا as binance_p2p_search.py ثم شغله: python binance_p2p_search.py
import requests

URL = "https://p2p.binance.com/bapi/c2c/v2/friendly/c2c/adv/search"

headers = {
    "BNC-Level": "0",
    "sec-ch-ua-platform": '"Windows"',
    "csrftoken": "d41d8cd98f00b204e9800998ecf8427e",
    "lang": "ar",
    "sec-ch-ua": '"Not;A=Brand";v="99", "Google Chrome";v="139", "Chromium";v="139"',
    "sec-ch-ua-mobile": "?0",
    "FVIDEO-ID": "336bccc70576ab05bd321511ff3d253b71c9b761",
    "BNC-UUID": "9076c8b6-bcb1-499e-b6e8-5e82c55d7c8b",
    #  curl 
    "X-PASSTHROUGH-TOKEN": "",
    "Content-Type": "application/json",
    "FVIDEO-TOKEN": "7AFPleOD41Jt9bDv2S2lH2RO4FONHIkJH2JjqtP05awzI/u2I7doE4GroaHgqiloANO1fk1eYbYJ/qLxiXaacc2/0dOc4HQT2ytzpXWbTGV1fygcOc7efBbYLwUR7qqAJeY3uO9bvxnORaUZgZd3wW5FvB5mly4UVgahgOM7BjGayMjr74tRl0ulzPzPG56wI=55",
    "Referer": "https://p2p.binance.com/trade/sell/USDT?fiat=EGP&payment=all-payments",
    "X-TRACE-ID": "0e7ae98f-4882-4af1-965b-6e65aef23a5b",
    "c2ctype": "c2c_web",
    "X-UI-REQUEST-TRACE": "0e7ae98f-4882-4af1-965b-6e65aef23a5b",
    "BNC-Time-Zone": "Africa/Cairo",
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36",
    "clienttype": "web",
    "BNC-Location": "EG",
    "device-info": "eyJzY3JlZW5fcmVzb2x1dGlvbiI6Ijk3OSwxNzM5IiwiYXZhaWxhYmxlX3NjcmVlbl9yZXNvbHV0aW9uIjoiOTc5LDE3MzkiLCJzeXN0ZW1fdmVyc2lvbiI6IldpbmRvd3MgMTAiLCJicmFuZF9tb2RlbCI6InVua25vd24iLCJzeXN0ZW1fbGFuZyI6ImVuLVVTIiwidGltZXpvbmUiOiJHTVQrMDM6MDAiLCJ0aW1lem9uZU9mZnNldCI6LTE4MCwidXNlcl9hZ2VudCI6Ik1vemlsbGEvNS4wIChXaW5kb3dzIE5UIDEwLjA7IFdpbjY0OyB4NjQpIEFwcGxlV2ViS2l0LzUzNy4zNiAoS0hUTUwsIGxpa2UgR2Vja28pIENocm9tZS8xMzkuMC4wLjAgU2FmYXJpLzUzNy4zNiIsImxpc3RfcGx1Z2luIjoiUERGIFZpZXdlcixDaHJvbWUgUERGIFZpZXdlcixDaHJvbWl1bSBQREYgVmlld2VyLE1pY3Jvc29mdCBFZGdlIFBERiBWaWV3ZXIsV2ViS2l0IGJ1aWx0LWluIFBERiIsImNhbnZhc19jb2RlIjoiZmI1ZTQyN2QiLCJ3ZWJnbF92ZW5kb3IiOiJHb29nbGUgSW5jLiAoSW50ZWwpIiwid2ViZ2xfcmVuZGVyZXIiOiJBTkdMRSAoSW50ZWwsIEludGVsKFIpIEhEIEdyYXBoaWNzIDQ2MDAgKDB4MDAwMDA0MTIpIERpcmVjdDNEMTEgdnNfNV8wIHBzXzVfMCwgRDNEMTEpIiwiYXVkaW8iOiIxMjQuMDQzNDc1Mjc1MTYwNzQiLCJwbGF0Zm9ybSI6IldpbjMyIiwid2ViX3RpbWV6b25lIjoiQWZyaWNhL0NhaXJvIiwiZGV2aWNlX25hbWUiOiJDaHJvbWUgVjEzOS4wLjAuMCAoV2luZG93cykiLCJmaW5nZXJwcmludCI6IjM5N2QwYmUzMTM3ZjM0Y2NlNGQ3YWUzZmE4Mzg1ODEzIiwiZGV2aWNlX2lkIjoiIiwicmVsYXRlZF9kZXZpY2VfaWRzIjoiIn0="
}

payload = {
    "fiat": "EGP",
    "page": 1,
    "rows": 10,
    "tradeType": "SELL",
    "asset": "USDT",
    "countries": [],
    "proMerchantAds": False,
    "shieldMerchantAds": False,
    "filterType": "all",
    "periods": [],
    "additionalKycVerifyFilter": 0,
    "publisherType": None,
    "payTypes": ["InstaPay"],
    "classifies": ["mass", "profession", "fiat_trade"],
    "tradedWith": False,
    "followed": False
}

def main():
    s = requests.Session()
    s.headers.update(headers)

    try:
        resp = s.post(URL, json=payload, timeout=15)  # timeout منطقي
    except requests.RequestException as e:
        print("خطأ في الاتصال:", e)
        return

    print("HTTP", resp.status_code)
    try:
        data = resp.json()
        print("مفتاح top-level keys:", list(data.keys()))
    except ValueError:
        print("Response is not JSON. Raw text:")
        print(resp.text[:1000])  # لعرض أول 1000 حرف فقط

if __name__ == "__main__":
    main()
