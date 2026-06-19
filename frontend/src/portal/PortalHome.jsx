import { useMemo } from 'react';
import { Link } from 'react-router-dom';
import { Ticket, CreditCard } from 'lucide-react';
import HeroCarousel from '../components/HeroCarousel';

function getWelcomeMessage() {
  const hour = new Date().getHours();
  let bucket;
  if (hour >= 5 && hour < 12) bucket = 'morning';
  else if (hour >= 12 && hour < 17) bucket = 'afternoon';
  else if (hour >= 17 && hour < 21) bucket = 'evening';
  else bucket = 'night';

  const messages = {
    morning: [
      'Good morning! Ready to get online?',
      'Rise and shine — your connection awaits.',
      'Morning! Grab a plan and start browsing.',
    ],
    afternoon: [
      'Good afternoon! Stay connected today.',
      'Afternoon vibes — unlimited browsing starts here.',
      'Halfway through the day — top up your internet.',
    ],
    evening: [
      'Good evening! Unwind with fast internet.',
      'Evening plans? We mean internet plans.',
      'Wind down — stream, browse, and connect.',
    ],
    night: [
      'Still up? The internet never sleeps.',
      'Night owl mode — stay connected.',
      'Late-night browsing? We got you.',
    ],
  };

  const variants = messages[bucket];
  return variants[Math.floor(Math.random() * variants.length)];
}

const PROMO_SLIDES = [
  {
    image: '/logo.png',
    title: 'Weekend Unlimited Pass',
    subtitle: 'Get 48 hours of uninterrupted browsing at a discounted rate.',
  },
  {
    image: '/logo.png',
    title: 'Refer a Friend',
    subtitle: 'Invite someone and both of you get bonus browsing time.',
  },
  {
    image: '/logo.png',
    title: 'New Customer Deal',
    subtitle: 'First purchase comes with an extra hour on the house.',
  },
];

export default function PortalHome() {
  const welcome = useMemo(() => getWelcomeMessage(), []);

  return (
    <div className="space-y-6 py-4">
      <div className="text-center space-y-2">
        <h1 className="text-xl font-bold text-slate-900 dark:text-slate-100">{welcome}</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400">
          Choose a plan, pay with mobile money, and get instant internet access.
        </p>
      </div>

      <HeroCarousel slides={PROMO_SLIDES} />

      <div className="grid gap-4">
        <Link
          to="/portal/plans"
          className="flex items-center gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-brand-300 dark:hover:border-brand-700 transition-colors"
        >
          <div className="w-10 h-10 bg-brand-50 dark:bg-brand-900/30 rounded-lg flex items-center justify-center shrink-0">
            <CreditCard className="w-5 h-5 text-brand-600 dark:text-brand-400" />
          </div>
          <div>
            <h3 className="font-semibold text-slate-900 dark:text-slate-100">Browse Plans</h3>
            <p className="text-sm text-slate-500 dark:text-slate-400">View available internet packages and pricing.</p>
          </div>
        </Link>

        <Link
          to="/portal/voucher"
          className="flex items-center gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-brand-300 dark:hover:border-brand-700 transition-colors"
        >
          <div className="w-10 h-10 bg-brand-50 dark:bg-brand-900/30 rounded-lg flex items-center justify-center shrink-0">
            <Ticket className="w-5 h-5 text-brand-600 dark:text-brand-400" />
          </div>
          <div>
            <h3 className="font-semibold text-slate-900 dark:text-slate-100">Enter Voucher</h3>
            <p className="text-sm text-slate-500 dark:text-slate-400">Already have a voucher code? Redeem it here.</p>
          </div>
        </Link>
      </div>
    </div>
  );
}
