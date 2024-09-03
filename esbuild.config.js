// esbuild.config.js
const esbuild = require('esbuild');
const fs = require('fs');
const path = require('path');

// Load environment variables from .env file
require('dotenv').config({ path: path.resolve(process.cwd(), `.env.${process.env.NODE_ENV}`) });

// Define the Shopify API key from the environment variables
const shopifyApiKey = process.env.VITE_SHOPIFY_API_KEY;

esbuild.build({
  entryPoints: ['resources/js/app.js'], // Entry point for your application
  bundle: true,
  outfile: 'public/js/app.js', // Output file
  plugins: [],
  define: {
    __SHOPIFY_API_KEY: JSON.stringify(shopifyApiKey),
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
  },
  loader: {
    '.js': 'jsx',
    '.jsx': 'jsx',
    '.ts': 'ts',
    '.tsx': 'tsx',
    '.css': 'css',
  },
  sourcemap: true,
  minify: process.env.NODE_ENV === 'production',
  target: ['esnext'],
}).catch(() => process.exit(1));
