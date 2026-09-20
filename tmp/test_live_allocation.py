import urllib.request, urllib.parse, http.cookiejar, json

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Get status & CSRF
req = urllib.request.Request('https://asim.eztoolbox.xyz/api.php?action=status', headers={'User-Agent': 'Mozilla/5.0'})
res = opener.open(req)
raw = res.read().decode('utf-8')
status_data = json.loads(raw)
csrf = status_data.get('csrf_token', '')

# 2. Login
login_data = urllib.parse.urlencode({'username': 'Hadi', 'password': '91199119', 'csrf_token': csrf}).encode('utf-8')
req = urllib.request.Request('https://asim.eztoolbox.xyz/api.php?action=login', data=login_data, headers={'User-Agent': 'Mozilla/5.0'})
opener.open(req)

# Read the 500 emails
with open('/tmp/test_500.csv', 'r') as f:
    csv_text = f.read()

print('CSV text length:', len(csv_text), 'lines:', len(csv_text.splitlines()))

# Test 1: save_order with csv_text
data_order = {
    'order_number': 'Order #1 Test',
    'domain': 'basis5.ch',
    'created_at': '2026-09-20 12:00:00',
    'rate_per_mail': 18,
    'total_price': 9000,
    'notes': 'Test Order Allocation',
    'csv_text': csv_text,
    'csrf_token': csrf
}

# Test sending as multipart/form-data or urlencoded
boundary = '----WebKitFormBoundaryTest7MA4YWxkTrZu0gW'
body_parts = []
for k, v in data_order.items():
    body_parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{v}\r\n')
body_parts.append(f'--{boundary}--\r\n')
multipart_body = ''.join(body_parts).encode('utf-8')

req = urllib.request.Request(
    'https://asim.eztoolbox.xyz/api.php?action=save_order',
    data=multipart_body,
    headers={
        'User-Agent': 'Mozilla/5.0',
        'Content-Type': f'multipart/form-data; boundary={boundary}'
    }
)

try:
    res = opener.open(req)
    out = res.read().decode('utf-8')
    print('save_order response code:', res.getcode())
    print('save_order response body (first 500 chars):', out[:500])
except urllib.error.HTTPError as e:
    print('HTTP Error:', e.code)
    print('Body:', e.read().decode('utf-8')[:500])
except Exception as ex:
    print('Exception:', ex)
