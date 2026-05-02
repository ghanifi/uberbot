const queries = require('../database/queries');

class AccountManager {
  constructor(accountsList) {
    this.accounts = accountsList;
  }

  async getNextAccount() {
    const allHealth = await queries.getAllAccountsHealth();

    // Filter to active (non-banned) accounts
    const activeAccounts = allHealth.filter(account => !account.is_banned);

    if (activeAccounts.length === 0) {
      throw new Error('No active accounts available');
    }

    // Return random active account
    const randomAccount = activeAccounts[Math.floor(Math.random() * activeAccounts.length)];

    // Find corresponding account from this.accounts list
    return this.accounts.find(acc => acc.email === randomAccount.account_email);
  }

  async recordSuccess(accountEmail) {
    await queries.updateAccountSuccess(accountEmail);
  }

  async recordFailure(accountEmail) {
    const health = await queries.updateAccountFailure(accountEmail);

    // Auto-ban if threshold exceeded
    if (health.failed_queries >= 5) {
      await queries.banAccount(accountEmail, 'Too many failed attempts');
      console.warn(`Account ${accountEmail} has been banned due to too many failed attempts`);
    }
  }

  async initializeAccounts() {
    for (const account of this.accounts) {
      const existing = await queries.getAccountHealth(account.email);

      if (!existing) {
        await queries.createAccount(account.email);
        console.log(`Initialized account: ${account.email}`);
      }
    }
  }

  async getHealthSummary() {
    const allHealth = await queries.getAllAccountsHealth();

    const totalAccounts = allHealth.length;
    const bannedAccounts = allHealth.filter(acc => acc.is_banned).length;
    const activeAccounts = totalAccounts - bannedAccounts;

    return {
      totalAccounts,
      activeAccounts,
      bannedAccounts,
      details: allHealth
    };
  }
}

module.exports = AccountManager;
