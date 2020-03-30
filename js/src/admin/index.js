import app from 'flarum/app';

import MicrosoftSettingsModal from './components/MicrosoftSettingsModal';

app.initializers.add('flarum-microsoft-auth', () => {
  app.extensionSettings['flarum-microsoft-auth'] = () => app.modal.show(new MicrosoftSettingsModal());
});
