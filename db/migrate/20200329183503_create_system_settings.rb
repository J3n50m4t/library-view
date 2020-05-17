class CreateSystemSettings < ActiveRecord::Migration[6.0]
  def change
    create_table :system_settings do |t|
      t.string :name, unique: true, null: false
      t.string :value, default: nil
      t.timestamps
    end
  end
end
