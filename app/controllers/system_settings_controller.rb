class SystemSettingsController < ApplicationController
  def edit
    test = nil
  end

  def index
    @settings = SystemSetting.all
  end

  def update
  setting = SystemSetting.find(params[:id])
  setting.assign_attributes(system_setting_params)
  if setting.save
    redirect_to system_settings_path
  else
    render :edit
  end
  
  end

  def system_setting_params
    params.require(:system_setting).permit(:value)
  end
end
