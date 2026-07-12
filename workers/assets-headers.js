/**
 * Force correct MIME for /sitemap.xml so browsers render XML (not HTML text).
 * When Content-Type is text/html, Chrome strips unknown tags and shows only URLs.
 */
export default {
  async fetch(request, env) {
    const url = new URL(request.url);
    const assetResponse = await env.ASSETS.fetch(request);

    if (url.pathname === '/sitemap.xml') {
      const headers = new Headers(assetResponse.headers);
      headers.set('Content-Type', 'application/xml; charset=utf-8');
      headers.set('X-Content-Type-Options', 'nosniff');
      headers.set('Cache-Control', 'public, max-age=3600');
      return new Response(assetResponse.body, {
        status: assetResponse.status,
        statusText: assetResponse.statusText,
        headers,
      });
    }

    return assetResponse;
  },
};
