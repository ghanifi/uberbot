const express = require('express');
const cors = require('cors');
const logger = require('../utils/logger');
const apiRoutes = require('./routes');

const createServer = () => {
  const app = express();

  // Middleware
  app.use(express.json());

  app.use(cors({
    origin: ['https://airlinel.com', 'http://localhost:3000', 'http://localhost:3001']
  }));

  // Request logger middleware
  app.use((req, res, next) => {
    logger.debug(`${req.method} ${req.path}`, { query: req.query });
    next();
  });

  // API routes
  app.use('/api', apiRoutes);

  // 404 handler
  app.use((req, res) => {
    res.status(404).json({ error: 'Not found' });
  });

  // Error handler
  app.use((err, req, res, next) => {
    logger.error(`Request error: ${req.method} ${req.path}`, err);
    res.status(500).json({ error: 'Internal server error' });
  });

  return app;
};

module.exports = createServer;
