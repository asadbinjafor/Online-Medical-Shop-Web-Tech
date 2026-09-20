const renderOrigin = 'https://online-medical-shop-web-tech.onrender.com';

export const config = {
  framework: null,
  rewrites: [
    {
      source: '/',
      destination: `${renderOrigin}/`,
    },
    {
      source: '/:path*',
      destination: `${renderOrigin}/:path*`,
    },
  ],
};
