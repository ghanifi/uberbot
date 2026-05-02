const fs = require('fs');
const path = require('path');

const logsDir = path.join(__dirname, '../../logs');

// Ensure logs directory exists
if (!fs.existsSync(logsDir)) {
  fs.mkdirSync(logsDir, { recursive: true });
}

const logFile = path.join(logsDir, 'scraper.log');

const formatMessage = (level, message, data) => {
  const timestamp = new Date().toISOString();
  const dataStr = data ? ` ${JSON.stringify(data)}` : '';
  return `[${timestamp}] [${level}] ${message}${dataStr}`;
};

const writeLog = (level, message, data) => {
  const logMessage = formatMessage(level, message, data);
  console.log(logMessage);

  try {
    fs.appendFileSync(logFile, logMessage + '\n');
  } catch (err) {
    console.error('Failed to write to log file:', err.message);
  }
};

module.exports = {
  info: (message, data) => writeLog('INFO', message, data),
  warn: (message, data) => writeLog('WARN', message, data),
  error: (message, err) => {
    const errorData = err ? { message: err.message, stack: err.stack } : null;
    writeLog('ERROR', message, errorData);
  },
  debug: (message, data) => {
    if (process.env.NODE_ENV !== 'production') {
      writeLog('DEBUG', message, data);
    }
  }
};
