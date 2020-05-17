class CreateLibraries < ActiveRecord::Migration[6.0]
  def change
    create_table :libraries do |t|
      t.string :name, null: false
      t.integer :plex_key, null: false
      t.string :library_type, null: false
      
      t.timestamps
    end
  end
end
