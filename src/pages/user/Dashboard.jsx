import DashboardLayout from "../../components/dashboard/DashboardLayout";

import BalanceCard from "../../components/dashboard/cards/BalanceCard";
import WalletCard from "../../components/dashboard/cards/WalletCard";
import OverviewCard from "../../components/dashboard/cards/OverviewCard";

import NotificationsCard from "../../components/dashboard/cards/NotificationsCard";
import PriceAlerts from "../../components/dashboard/cards/PriceAlerts";
import EconomicCalendar from "../../components/dashboard/cards/EconomicCalendar";
import NewsFeed from "../../components/dashboard/cards/NewsFeed";

import TradingWorkspace from "../../components/dashboard/trading/TradingWorkspace";

import TradeProvider from "../../context/TradeContext";

import useDashboard from "../../hooks/useDashboard";

export default function Dashboard() {
    const { dashboard, loading, error } = useDashboard();

    if (loading) {
        return (
            <DashboardLayout>
                <div className="flex items-center justify-center h-full min-h-[70vh]">
                    <div className="text-center">
                        <h2 className="text-2xl font-bold text-white">
                            Loading Dashboard...
                        </h2>

                        <p className="text-gray-400 mt-2">
                            Please wait...
                        </p>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    if (error) {
        return (
            <DashboardLayout>
                <div className="flex items-center justify-center h-full min-h-[70vh]">
                    <div className="bg-red-500/10 border border-red-500/20 rounded-2xl p-8 max-w-lg w-full">
                        <h2 className="text-red-500 text-xl font-bold">
                            Failed loading dashboard
                        </h2>

                        <p className="text-gray-300 mt-3">
                            {error?.response?.data?.message ||
                                error?.message ||
                                "Unknown server error."}
                        </p>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    return (
        <TradeProvider>
            <DashboardLayout>

                {/* Top Cards */}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <BalanceCard data={dashboard} />

                    <WalletCard data={dashboard} />

                    <OverviewCard data={dashboard} />

                </div>

                {/* Notification */}

                <div className="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">

                    <NotificationsCard data={dashboard} />

                    <PriceAlerts data={dashboard} />

                </div>

                {/* News */}

                <div className="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">

                    <EconomicCalendar data={dashboard} />

                    <NewsFeed data={dashboard} />

                </div>

                {/* Trading */}

                <div className="mt-6">

                    <TradingWorkspace data={dashboard} />

                </div>

            </DashboardLayout>
        </TradeProvider>
    );
}
