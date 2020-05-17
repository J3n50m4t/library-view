Rails.application.routes.draw do
  get 'libraries/show/:id', to: 'libraries#show', :as => :show_library
  resources :manage
  post 'add_library', to: 'manage#add_library'

  resources :system_settings

  root 'system_settings#index'
  # For details on the DSL available within this file, see http://guides.rubyonrails.org/routing.html
end
