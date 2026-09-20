const renderOrigin = process.env.RENDER_ORIGIN?.replace(/\/+$/, '');

if (!renderOrigin || !/^https:\/\/[^/]+$/.test(renderOrigin)) {
  throw new Error('Set RENDER_ORIGIN to the HTTPS Render service URL in Vercel project settings.');
}

export const config = {
  framework: null,
  rewrites: [
    { source: '/', destination: `${renderOrigin}/` },
    { source: '/:path*', destination: `${renderOrigin}/:path*` },
  ],
};
