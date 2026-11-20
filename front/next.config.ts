import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  /* config options here */
  experimental: {
    agentFeedback: true,
  },
  cacheComponents: true,
  partialPrefetching: true,
  reactCompiler: true,
  turbopack: {
    rules: {
      "*.css": {
        loaders: ["@tailwindcss/turbopack"],
        as: "*.css",
      },
    },
  },
  output: "standalone",
  images: {
    remotePatterns: [
        new URL('http://localhost:3000/**')
    ],
    formats: ['image/avif'],
    dangerouslyAllowLocalIP: true
  },
};

export default nextConfig;
