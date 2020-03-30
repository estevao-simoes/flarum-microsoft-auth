import SettingsModal from 'flarum/components/SettingsModal';

export default class MicrosoftkSettingsModal extends SettingsModal {
  className() {
    return 'MicrosoftSettingsModal Modal--small';
  }

  title() {
    return app.translator.trans('flarum-microsoft-auth.admin.microsoft_settings.title');
  }

  form() {
    return [
      <div className="Form-group">
        <label>{app.translator.trans('flarum-microsoft-auth.admin.microsoft_settings.client_id_label')}</label>
        <input className="FormControl" bidi={this.setting('flarum-microsoft-auth.client_id')}/>
      </div>,

      <div className="Form-group">
        <label>{app.translator.trans('flarum-microsoft-auth.admin.microsoft_settings.client_secret_label')}</label>
        <input className="FormControl" bidi={this.setting('flarum-microsoft-auth.client_secret')}/>
      </div>
    ];
  }
}
