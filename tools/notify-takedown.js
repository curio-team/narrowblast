const dotenv = require('dotenv');
const crypto = require('crypto');
const path = require('node:path');

const args = process.argv.slice(2);
const url = args[0];
let timestamp = args[1];

// Validate the URL
if (!url) {
  console.error('Please provide a URL to notify');
  process.exit(1);
}

const protocol = new URL(url).protocol;
const hostname = new URL(url).hostname;
const port = new URL(url).port;

// Validate the timestamp
if (!timestamp) {
  console.error('Please provide a time to notify');
  process.exit(1);
}

// Try parse from from Y-m-d\TH:i format
if(isNaN(timestamp)) {
  timestamp = Date.parse(timestamp) / 1000;
}

timestamp = `${timestamp}`;

dotenv.config({ path: path.resolve(process.cwd(), '.env') });

console.log('Notifying takedown of', hostname, '(', protocol, ')','at', new Date(timestamp * 1000).toISOString());
console.log('Current time is', new Date().toISOString());

// Sign the timestamp
const signed_timestamp = crypto.createHmac('sha1', process.env.WEBHOOK_SECRET).update(timestamp).digest('hex');

let mode;

if(protocol === 'http:') {
  mode = require('node:http');
} else {
  mode = require('node:https');
}

const req = mode.request({
  protocol: protocol,
  hostname: hostname,
  port: port,
  path: '/update-deployment.php',
  method: 'POST',
  headers: {
    'Content-Type': 'text/plain',
    'X-TIMEOUT-SIGNATURE': `sha1=${signed_timestamp}`,
  }
}, (res) => {
  console.log(`STATUS: ${res.statusCode}`);
  console.log(`HEADERS: ${JSON.stringify(res.headers)}`);
  res.setEncoding('utf8');
  res.on('data', (chunk) => {
    console.log(`BODY: ${chunk}`);
  });
  res.on('end', () => {
    console.log('No more data in response.');
  });
});

req.on('error', (e) => {
  console.error(`problem with request: ${e.message}`);
});

// Write data to request body
req.write(timestamp);
req.end();
