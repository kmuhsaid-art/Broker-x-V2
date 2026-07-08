import React, { useState, useEffect } from 'react';
import axios from 'axios';

const Dashboard = () => {
  const [userData, setUserData] = useState(null);
  const [wallets, setWallets] = useState([]);
  const [exchangeRates, setExchangeRates] = useState({});
  const [openOrders, setOpenOrders] = useState(0);
  const [openPositions, setOpenPositions] = useState(0);
  const [recentTrades, setRecentTrades] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Fungsi format currency dinamis berdasarkan tipe mata uang
  const formatCurrency = (amount, currency = 'USD') => {
    if (!amount && amount !== 0) return '-';
    
    const configs = {
        IDR: { symbol: 'Rp ', locale: 'id-ID', decimals: 0 },
        USD: { symbol: '$', locale: 'en-US', decimals: 2 },
        USDT: { symbol: '', locale: 'en-US', decimals: 2 },
        BTC: { symbol: '₿', locale: 'en-US', decimals: 8 },
        ETH: { symbol: 'Ξ', locale: 'en-US', decimals: 6 },
    };

    const config = configs[currency] || configs['USD'];
    
    return new Intl.NumberFormat(config.locale, {
        minimumFractionDigits: config.decimals,
        maximumFractionDigits: config.decimals,
    }).format(amount) + (config.symbol === 'Rp ' ? ' ' + config.symbol : config.symbol);
};

    const config = configs[currency] || { symbol: '', locale: 'en-US', decimals: 2 };
    const formatted = new Intl.NumberFormat(config.locale, {
      minimumFractionDigits: config.decimals,
      maximumFractionDigits: config.decimals,
    }).format(amount);

    return `${config.symbol}${formatted}${config.suffix || ''}`;
  };

  // Hitung total balance dalam USD
  const calculateTotalBalanceUSD = () => {
    return wallets.reduce((total, wallet) => {
      const rate = exchangeRates[wallet.currency] || 1;
      return total + (wallet.balance * rate);
    }, 0);
  };

  // Fetch semua data dashboard
  const fetchData = async () => {
    try {
      setLoading(true);
      setError(null);
      const token = localStorage.getItem('brokerx_token');
      
      if (!token) {
        throw new Error('Token tidak ditemukan. Silakan login ulang.');
      }

      const headers = { Authorization: `Bearer ${token}` };
      const baseUrl = 'http://127.0.0.1:8000/api';

      const [userRes, walletsRes, ratesRes, ordersRes, positionsRes, tradesRes] = await Promise.all([
        axios.get(`${baseUrl}/user`, { headers }),
        axios.get(`${baseUrl}/wallets`, { headers }),
        axios.get(`${baseUrl}/exchange-rates`, { headers }),
        axios.get(`${baseUrl}/orders?status=open`, { headers }),
        axios.get(`${baseUrl}/positions?status=open`, { headers }),
        axios.get(`${baseUrl}/trades?limit=10`, { headers }),
      ]);

      setUserData(userRes.data);
      setWallets(walletsRes.data);
      setExchangeRates(ratesRes.data);
      setOpenOrders(ordersRes.data.length || 0);
      setOpenPositions(positionsRes.data.length || 0);
      setRecentTrades(tradesRes.data.length || 0);
    } catch (err) {
      console.error('Dashboard fetch error:', err);
      setError(err.response?.data?.message || 'Gagal memuat data dashboard. Silakan refresh halaman.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  // Tampilan loading state
  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen bg-gray-900">
        <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-yellow-500"></div>
      </div>
    );
  }

  // Tampilan error state
  if (error) {
    return (
      <div className="p-8 max-w-md mx-auto mt-20 text-center">
        <div className="bg-red-900/50 border border-red-700 p-6 rounded-lg">
          <p className="text-red-200 mb-4">{error}</p>
          <button 
            onClick={fetchData}
            className="px-4 py-2 bg-red-700 hover:bg-red-600 text-white rounded transition"
          >
            Coba Lagi
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="p-6 max-w-7xl mx-auto space-y-8">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-white">Dashboard</h1>
          <p className="text-gray-400 mt-1">Selamat datang kembali, {userData?.name || 'Trader'}!</p>
        </div>
        <button 
          onClick={fetchData}
          className="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition border border-gray-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clipRule="evenodd" />
          </svg>
          Refresh Data
        </button>
      </div>

      {/* Total Balance Card */}
      <div className="bg-gradient-to-br from-blue-600 to-blue-800 p-6 rounded-xl shadow-lg">
        <h3 className="text-blue-200 text-sm font-medium uppercase tracking-wider">Total Balance (Estimasi USD)</h3>
        <p className="text-4xl font-bold text-white mt-2">
          ${calculateTotalBalanceUSD().toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </p>
        <p className="text-blue-300 text-xs mt-2">*Nilai estimasi berdasarkan kurs saat ini</p>
      </div>

      {/* Wallet List Section */}
      <div className="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 className="text-xl font-bold text-white mb-4 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          My Wallets ({wallets.length})
        </h3>
        
        {wallets.length === 0 ? (
          <div className="text-center py-8 text-gray-400 bg-gray-900/50 rounded-lg">
            Belum ada wallet aktif. Silakan buat wallet baru.
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {wallets.map((wallet) => (
              <div 
                key={wallet.id} 
                className="bg-gray-700/50 hover:bg-gray-700 p-4 rounded-lg border border-gray-600 transition-all duration-200"
              >
                <div className="flex justify-between items-start">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-yellow-500/20 rounded-full flex items-center justify-center text-yellow-400 font-bold text-lg">
                      {wallet.currency.substring(0, 1)}
                    </div>
                    <div>
                      <p className="text-white font-semibold">{wallet.currency}</p>
                      <p className="text-gray-400 text-xs">ID: {wallet.id}</p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="text-white font-bold text-lg">
                      {formatCurrency(wallet.balance, wallet.currency)}
                    </p>
                    <p className="text-gray-400 text-xs">
                      ≈ ${(wallet.balance * (exchangeRates[wallet.currency] || 1)).toFixed(2)} USD
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Overview Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          { label: 'Open Orders', value: openOrders, color: 'blue', icon: '📋' },
          { label: 'Open Positions', value: openPositions, color: 'green', icon: '📈' },
          { label: 'Recent Trades', value: recentTrades, color: 'purple', icon: '💹' },
          { label: 'Active Wallets', value: wallets.length, color: 'yellow', icon: '' },
        ].map((stat, idx) => (
          <div key={idx} className={`bg-${stat.color}-900/30 border border-${stat.color}-700/50 p-4 rounded-lg`}>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-400 text-sm">{stat.label}</p>
                <p className="text-2xl font-bold text-white mt-1">{stat.value}</p>
              </div>
              <span className="text-3xl opacity-80">{stat.icon}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Dashboard;
