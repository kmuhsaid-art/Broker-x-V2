import React, { useState, useEffect } from 'react';
import axios from 'axios';

const Dashboard = () => {
    // 1. Inisialisasi State yang Benar (Array/Object, bukan angka 0)
    const [wallets, setWallets] = useState([]);
    const [exchangeRates, setExchangeRates] = useState({});
    const [openOrders, setOpenOrders] = useState([]);
    const [positions, setPositions] = useState([]);
    const [recentTrades, setRecentTrades] = useState([]);
    
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // 2. Fungsi Format Currency yang Aman
    const formatCurrency = (amount, currency = 'USD') => {
        if (amount === null || amount === undefined) return '-';
        
        const configs = {
            IDR: { symbol: 'Rp ', locale: 'id-ID', decimals: 0 },
            USD: { symbol: '$', locale: 'en-US', decimals: 2 },
            USDT: { symbol: '', locale: 'en-US', decimals: 2 },
            BTC: { symbol: '₿', locale: 'en-US', decimals: 8 },
            ETH: { symbol: 'Ξ', locale: 'en-US', decimals: 6 },
        };

        const config = configs[currency] || configs['USD'];
        
        try {
            const formatted = new Intl.NumberFormat(config.locale, {
                minimumFractionDigits: config.decimals,
                maximumFractionDigits: config.decimals,
            }).format(amount);
            
            return config.symbol === 'Rp ' ? `${formatted} ${config.symbol}` : `${config.symbol}${formatted}`;
        } catch (e) {
            return String(amount);
        }
    };

    // 3. Fetch Data dengan Token yang Benar
    const fetchData = async () => {
        try {
            setLoading(true);
            const token = localStorage.getItem('brokerx_token'); // PERBAIKAN KEY TOKEN
            
            if (!token) {
                throw new Error('Token tidak ditemukan. Silakan login ulang.');
            }

            const headers = { Authorization: `Bearer ${token}` };
            const baseURL = 'http://127.0.0.1:8000/api';

            // Gunakan Promise.allSettled agar satu error tidak menggagalkan semua
            const results = await Promise.allSettled([
                axios.get(`${baseURL}/wallets`, { headers }),
                axios.get(`${baseURL}/exchange-rates`, { headers }),
                axios.get(`${baseURL}/orders?status=open`, { headers }),
                axios.get(`${baseURL}/positions?status=open`, { headers }),
                axios.get(`${baseURL}/trades?limit=10`, { headers }),
            ]);

            // Mapping hasil dengan fallback array kosong jika gagal
            setWallets(results[0].status === 'fulfilled' ? results[0].value.data : []);
            setExchangeRates(results[1].status === 'fulfilled' ? results[1].value.data : {});
            setOpenOrders(results[2].status === 'fulfilled' ? results[2].value.data : []);
            setPositions(results[3].status === 'fulfilled' ? results[3].value.data : []);
            setRecentTrades(results[4].status === 'fulfilled' ? results[4].value.data : []);

        } catch (err) {
            console.error("Dashboard fetch error:", err);
            setError(err.message || "Gagal memuat data dashboard");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    // Hitung Total Balance dalam USD
    const calculateTotalBalanceUSD = () => {
        let total = 0;
        wallets.forEach(wallet => {
            const rate = exchangeRates[wallet.currency] || 1;
            total += parseFloat(wallet.balance) * rate;
        });
        return total;
    };

    if (loading) return <div className="p-8 text-white">Memuat Dashboard...</div>;
    if (error) return <div className="p-8 text-red-500">Error: {error}</div>;

    return (
        <div className="p-6 space-y-6 bg-gray-900 min-h-screen text-white">
            <h1 className="text-3xl font-bold">Dashboard</h1>
            
            {/* Kartu Total Balance */}
            <div className="bg-gray-800 p-6 rounded-lg shadow-lg">
                <p className="text-gray-400 text-sm">Total Portfolio Value (USD)</p>
                <p className="text-3xl font-bold mt-2">{formatCurrency(calculateTotalBalanceUSD(), 'USD')}</p>
            </div>

            {/* Daftar Wallet */}
            <div className="bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 className="text-xl font-semibold mb-4">My Wallets</h2>
                {wallets.length === 0 ? (
                    <p className="text-gray-500">Belum ada wallet.</p>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {wallets.map((w, idx) => (
                            <div key={idx} className="bg-gray-700 p-4 rounded border border-gray-600">
                                <p className="font-bold text-lg">{w.currency}</p>
                                <p className="text-2xl mt-1">{formatCurrency(w.balance, w.currency)}</p>
                                <p className="text-xs text-gray-400 mt-1">Locked: {formatCurrency(w.locked_balance, w.currency)}</p>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Statistik Orders & Positions */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h2 className="text-xl font-semibold mb-4">Open Orders ({openOrders.length})</h2>
                    <p className="text-gray-400 text-sm">Menunggu eksekusi pasar...</p>
                </div>
                <div className="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h2 className="text-xl font-semibold mb-4">Active Positions ({positions.length})</h2>
                    <p className="text-gray-400 text-sm">Posisi trading aktif saat ini...</p>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;

