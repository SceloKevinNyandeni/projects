package za.ac.cput.Domain;

import jakarta.persistence.*;
import java.util.List;

@Entity
public class Book {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    protected Long bookID;

    protected String title;

    @ManyToMany
    @JoinTable(
            name = "book_author",  // Name of the join table
            joinColumns = @JoinColumn(name = "book_id"),  // Foreign key for Book
            inverseJoinColumns = @JoinColumn(name = "author_id")  // Foreign key for Author
    )
    protected List<Author> authors;

    @ManyToMany(cascade = CascadeType.ALL)
    @JoinTable(
            name = "book_publisher",
            inverseJoinColumns = @JoinColumn(name = "publisher_id")

    )
    protected List<Publisher> publishers;

    protected int year;
    protected double price;

    public Book(){

    }

    public Book(BookBuilder bookBuilder){
        this.bookID = bookBuilder.bookID;
        this.title = bookBuilder.title;
        this.authors = bookBuilder.authors;
        this.publishers = bookBuilder.publishers;
        this.year = bookBuilder.year;
        this.price = bookBuilder.price;
    }

    public Long getBookID() {
        return bookID;
    }

    public String getTitle() {
        return title;
    }

    public List<Author> getAuthors() {
        return authors;
    }

    public List<Publisher> getPublishers() {
        return publishers;
    }

    public int getYear() {
        return year;
    }

    public double getPrice() {
        return price;
    }

    @Override
    public String toString() {
        return "Book{" +
                "BookID=" + bookID +
                ", title='" + title + '\'' +
                ", authors=" + authors +
                ", publishers=" + publishers +
                ", year=" + year +
                ", price=" + price +
                '}';
    }

    public static class BookBuilder{
        private Long bookID;
        private String title;
        private List<Author> authors;
        private List<Publisher> publishers;
        private int year;
        private double price;
        public BookBuilder(){

        }

        public BookBuilder setBookID(Long bookID) {
            this.bookID = bookID;
            return this;
        }

        public BookBuilder setTitle(String title) {
            this.title = title;
            return this;
        }

        public BookBuilder setAuthors(List<Author> authors) {
            this.authors = authors;
            return this;
        }

        public BookBuilder setPublishers(List<Publisher> publishers) {
            this.publishers = publishers;
            return this;
        }

        public BookBuilder setYear(int year) {
            this.year = year;
            return this;
        }

        public BookBuilder setPrice(double price) {
            this.price = price;
            return this;
        }
        public Book build(){
            return new Book(this);
        }
    }
}
