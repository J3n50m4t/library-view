Rails.application.routes.draw do
  get 'administration/index'
  root 'administration#index'
  # For details on the DSL available within this file, see http://guides.rubyonrails.org/routing.html
end
