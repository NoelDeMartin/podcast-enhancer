import { showModal } from '@noeldemartin/vue-modals';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { onMounted } from 'vue';

import { ModalsPortal } from '@/components/ui/modal';
import { entries } from '@/testing/stubs/entries';
import { feeds } from '@/testing/stubs/feeds';

import FailureModal from './FailureModal.vue';

type FailureModalStoryArgs = {
    title: string;
    description?: string | null;
    details?: string | null;
};

const meta: Meta<FailureModalStoryArgs> = {
    title: 'Modals/FailureModal',
    component: FailureModal,
    render: (args) => ({
        components: { ModalsPortal },
        setup() {
            onMounted(() => {
                void showModal(FailureModal, args);
            });
            return {};
        },
        template: '<ModalsPortal />',
    }),
};

export default meta;
type Story = StoryObj<typeof meta>;

export const ProcessingFailed: Story = {
    args: {
        title: 'Processing failed',
        description: entries[0].name,
        details: 'Something went wrong during transcription.',
    },
};

export const SynchronizationFailed: Story = {
    args: {
        title: 'Synchronization failed',
        description: feeds[0].title,
        details: `ErrorException: Trying to access array offset on value of type null in /var/www/html/app/Jobs/SyncFeedJob.php:87
Stack trace:
#0 /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Bootstrap/HandleExceptions.php(269): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, 'Trying to access...', '/var/www/html/a...', 87)
#1 /var/www/html/app/Jobs/SyncFeedJob.php(87): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->{closure:Illuminate\\Foundation\\Bootstrap\\HandleExceptions::forwardsTo():268}()
#2 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\SyncFeedJob->handle()
#3 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Util.php(43): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))
#4 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(96): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))
#5 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))
#6 /var/www/html/vendor/laravel/framework/src/Illuminate/Container/Container.php(776): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)
#7 /var/www/html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(126): Illuminate\\Container\\Container->call(Array)
#8 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(170): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SyncFeedJob))
#9 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SyncFeedJob))
#10 /var/www/html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(130): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))
#11 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(124): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SyncFeedJob), false)
#12 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(170): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SyncFeedJob))
#13 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Middleware/WithoutOverlapping.php(71): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SyncFeedJob))
#14 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Queue\\Middleware\\WithoutOverlapping->handle(Object(App\\Jobs\\SyncFeedJob), Object(Closure))
#15 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SyncFeedJob))
#16 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(123): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))
#17 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(71): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\SyncJob), Object(App\\Jobs\\SyncFeedJob))
#18 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\SyncJob), Array)
#19 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/SyncQueue.php(78): Illuminate\\Queue\\Jobs\\Job->fire()
#20 /var/www/html/vendor/laravel/framework/src/Illuminate/Queue/Queue.php(143): Illuminate\\Queue\\SyncQueue->push(Object(Illuminate\\Queue\\CallQueuedClosure), '', '')
#21 /var/www/html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(254): Illuminate\\Queue\\Queue->pushOn(NULL, Object(Illuminate\\Queue\\CallQueuedClosure))
#22 /var/www/html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(227): Illuminate\\Bus\\Dispatcher->pushCommandToQueue(NULL, Object(Illuminate\\Queue\\CallQueuedClosure))
#23 /var/www/html/vendor/laravel/framework/src/Illuminate/Bus/Dispatcher.php(77): Illuminate\\Bus\\Dispatcher->dispatchToQueue(Object(Illuminate\\Queue\\CallQueuedClosure))
#24 /var/www/html/app/Http/Controllers/FeedSyncController.php(28): Illuminate\\Bus\\Dispatcher->dispatch(Object(App\\Jobs\\SyncFeedJob))
#25 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(46): App\\Http\\Controllers\\FeedSyncController->__invoke(Object(App\\Models\\Feed))
#26 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Route.php(272): Illuminate\\Routing\\ControllerDispatcher->dispatch(Object(Illuminate\\Routing\\Route), Object(App\\Http\\Controllers\\FeedSyncController), '__invoke')
#27 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Route.php(218): Illuminate\\Routing\\Route->runController()
#28 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Router.php(797): Illuminate\\Routing\\Route->run()
#29 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(170): Illuminate\\Routing\\Router->Illuminate\\Routing\\{closure}(Object(Illuminate\\Http\\Request))
#30 /var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Middleware/SubstituteBindings.php(51): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))
#31 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(208): Illuminate\\Routing\\Middleware\\SubstituteBindings->handle(Object(Illuminate\\Http\\Request), Object(Closure))
#32 /var/www/html/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(Illuminate\\Http\\Request))
#33 /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php(176): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))
#34 /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php(145): Illuminate\\Foundation\\Http\\Kernel->sendRequestThroughRouter(Object(Illuminate\\Http\\Request))
#35 /var/www/html/public/index.php(20): Illuminate\\Foundation\\Http\\Kernel->handle(Object(Illuminate\\Http\\Request))
#36 {main}`,
    },
};
