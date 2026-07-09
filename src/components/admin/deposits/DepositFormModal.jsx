import { useState } from 'react';
import { X, UploadCloud } from 'lucide-react'; // Import icon Lucide sesuai gaya kamu
import axios from 'axios';

export default function DepositFormModal({ isOpen, onClose, onSuccess }) {
  const [amount, setAmount] = useState('');
  const [currency, setCurrency] = useState('USD');
  const [loading, setLoading] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    
    try {
      const formData = new FormData();
      formData.append('amount', amount);
      formData.append('currency', currency);
      formData.append('payment_method_id', 1); // Sesuaikan dengan dropdown metode pembayaran
      
      // Kirim ke backend Laravel
      await axios.post('/api/deposits', formData, {
        headers: { 
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${localStorage.getItem('token')}` 
        }
      });

      onSuccess(); // Callback untuk refresh tabel/statistik
      onClose();   // Tutup modal
    } catch (err) {
      alert(err.response?.data?.message || 'Deposit gagal');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-gray-900 border border-gray-700 rounded-xl p-6 w-full max-w-md relative">
        
        {/* Header Modal */}
        <div className="flex justify-between items-center mb-4">
          <h3 className="text-xl font-bold text-white">Request Deposit</h3>
          <button onClick={onClose} className="text-gray-400 hover:text-white">
            <X size={20} />
          </button>
        </div>

        {/* Form Input */}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm text-gray-400 mb-1">Currency</label>
            <select 
              value={currency} 
              onChange={(e) => setCurrency(e.target.value)}
              className="w-full bg-gray-800 border border-gray-700 rounded-lg p-2 text-white focus:border-blue-500 outline-none"
            >
              <option value="USD">USD</option>
              <option value="IDR">IDR</option>
            </select>
          </div>

          <div>
            <label className="block text-sm text-gray-400 mb-1">Amount</label>
            <input 
              type="number" 
              min="10"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              className="w-full bg-gray-800 border border-gray-700 rounded-lg p-2 text-white focus:border-blue-500 outline-none"
              placeholder="Min. 10"
              required
            />
          </div>

          <button 
            type="submit" 
            disabled={loading}
            className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition-colors disabled:opacity-50"
          >
            {loading ? 'Processing...' : 'Submit Deposit'}
          </button>
        </form>

      </div>
    </div>
  );
}

