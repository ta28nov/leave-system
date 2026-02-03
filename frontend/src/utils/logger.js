/**
 * Logger utility for centralized error and info logging
 * In production, this can be extended to send logs to a logging service
 */

const isDev = import.meta.env.DEV

/**
 * Log levels
 */
const LogLevel = {
  DEBUG: 'debug',
  INFO: 'info',
  WARN: 'warn',
  ERROR: 'error'
}

/**
 * Format log message with timestamp
 */
const formatMessage = (level, context, message, data) => {
  const timestamp = new Date().toISOString()
  return {
    timestamp,
    level,
    context,
    message,
    data
  }
}

/**
 * Log to console (only in development)
 */
const logToConsole = (logEntry) => {
  if (!isDev) return

  const { level, context, message, data } = logEntry
  const prefix = `[${context}]`

  switch (level) {
    case LogLevel.DEBUG:
      console.debug(prefix, message, data || '')
      break
    case LogLevel.INFO:
      console.info(prefix, message, data || '')
      break
    case LogLevel.WARN:
      console.warn(prefix, message, data || '')
      break
    case LogLevel.ERROR:
      console.error(prefix, message, data || '')
      break
    default:
      console.log(prefix, message, data || '')
  }
}

/**
 * Logger instance
 */
export const logger = {
  debug: (context, message, data) => {
    const entry = formatMessage(LogLevel.DEBUG, context, message, data)
    logToConsole(entry)
  },

  info: (context, message, data) => {
    const entry = formatMessage(LogLevel.INFO, context, message, data)
    logToConsole(entry)
  },

  warn: (context, message, data) => {
    const entry = formatMessage(LogLevel.WARN, context, message, data)
    logToConsole(entry)
  },

  error: (context, message, data) => {
    const entry = formatMessage(LogLevel.ERROR, context, message, data)
    logToConsole(entry)
    
    // In production, you could send this to a logging service
    // Example: sendToLoggingService(entry)
  },

  /**
   * Log API error with structured data
   */
  apiError: (endpoint, status, message, details) => {
    const entry = formatMessage(LogLevel.ERROR, 'API', message, {
      endpoint,
      status,
      details
    })
    logToConsole(entry)
  }
}

export default logger
