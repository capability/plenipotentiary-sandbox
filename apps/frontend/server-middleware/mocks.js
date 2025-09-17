const fs = require('fs'); const path = require('path'); const url = require('url');
module.exports = (req, res) => {
  const { pathname } = url.parse(req.url, true);
  if (pathname === '/ebay/search') {
    const raw = fs.readFileSync(path.join(process.cwd(),'mocks','ebay-search.json'),'utf8');
    res.setHeader('Content-Type','application/json'); res.end(raw); return;
  }
  res.statusCode = 404; res.end(JSON.stringify({ error: 'Mock route not found' }));
};

