# Proyecto Terminal

This workspace contains a simple login page demo with Google reCAPTCHA v2 integration.

Setup
-----

Edit `index.html` and replace `YOUR_RECAPTCHA_SITE_KEY` with your reCAPTCHA **site key**.
Keep your **secret key** on the server and use it to verify tokens from the client.

Quick test (static site)
-------------------------

Serve the folder locally and open http://localhost:8000/

```bash
python3 -m http.server 8000
```

Server-side verification (example)
----------------------------------

Example Node.js/Express verification using `node-fetch` (install with `npm install node-fetch@2`):

```js
const fetch = require('node-fetch');

async function verifyRecaptcha(token) {
	const secret = process.env.RECAPTCHA_SECRET; // keep this secret on server
	const res = await fetch(`https://www.google.com/recaptcha/api/siteverify`, {
		method: 'POST',
		headers: {'Content-Type':'application/x-www-form-urlencoded'},
		body: `secret=${encodeURIComponent(secret)}&response=${encodeURIComponent(token)}`
	});
	return res.json();
}

// In your /login handler:
// const result = await verifyRecaptcha(req.body['g-recaptcha-response']);
// if (!result.success) return res.status(403).json({ error: 'reCAPTCHA verification failed' });
```

Replace the `/login` endpoint in the client script with your own backend route. Verify `result.success` and any risk scores returned by reCAPTCHA before accepting the login.

Notes
-----
- This example uses reCAPTCHA v2 (checkbox). For an invisible or v3 flow adjust client and server logic accordingly.
- Do not embed the secret key in client-side code.
# proyecto-terminal
Sistema web para titulación
