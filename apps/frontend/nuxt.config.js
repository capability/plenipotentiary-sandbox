export default {
  telemetry: false,
  server: { host: '0.0.0.0', port: 3000 },
  watchers: {
    // helps with bind mounts on macOS containers
    webpack: { poll: process.env.WEBPACK_POLL ? Number(process.env.WEBPACK_POLL) : false }
  }
};