package za.ac.cput.Domain;

import jakarta.persistence.*;

import java.util.List;

@Entity
public class Publisher {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long publisherId;

    private String publisherName;

    @ManyToMany(mappedBy = "publishers")
    private List<Book> books;

    public Publisher() {

    }
    public Publisher(PublisherBuilder publisherBuilder){
        this.publisherId = publisherBuilder.publisherId;
        this.publisherName = publisherBuilder.publisherName;
        this.books = publisherBuilder.books;
    }

    public Long getPublisherId() {
        return publisherId;
    }

    public String getPublisherName() {
        return publisherName;
    }

    public List<Book> getBooks() {
        return books;
    }

    @Override
    public String toString() {
        return "Publisher{" +
                "publisherId=" + publisherId +
                ", publisherName='" + publisherName + '\'' +
                ", books=" + books +
                '}';
    }

    public static class PublisherBuilder{
        private Long publisherId;
        private String publisherName;
        private List<Book> books;

        public PublisherBuilder() {

        }
        public PublisherBuilder setPublisherId(Long publisherId) {
            this.publisherId = publisherId;
            return this;
        }
        public PublisherBuilder setPublisherName(String publisherName) {
            this.publisherName = publisherName;
            return this;
        }
        public PublisherBuilder setBooks(List<Book> books) {
            this.books = books;
            return this;
        }
        public Publisher build(){
            return new Publisher(this);
        }
    }
}
