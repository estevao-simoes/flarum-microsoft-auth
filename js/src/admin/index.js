import app from 'flarum/app';

import MicrosoftSettingsModal from './components/MicrosoftSettingsModal';

app.initializers.add('estevao-simoes-microsoft-auth', () => {
  app.extensionSettings['estevao-simoes-microsoft-auth'] = () => app.modal.show(new MicrosoftSettingsModal());
});
