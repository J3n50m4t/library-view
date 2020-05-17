class CreateLibraryEntries < ActiveRecord::Migration[6.0]
  def change
    create_table :library_entries do |t|
      t.string :title, null: false
      t.references :library, null: false
      t.string :imagepath
      t.string :rating
      t.string :key, null: false

      t.timestamps
    end
  end
end
